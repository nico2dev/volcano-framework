<?php

namespace Volcano\Localization\Console;

use Volcano\Config\Repository as Config;

use Volcano\Console\Command;

use Volcano\Filesystem\FileNotFoundException;
use Volcano\Filesystem\Filesystem;

use Volcano\Localization\LanguageManager;

use Volcano\Support\Arr;
use Volcano\Support\Str;

use Symfony\Component\Console\Input\InputOption;

use Exception;
use Throwable;


class LanguagesUpdateCommand extends Command
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'language:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Language files';

    /**
     * The Language Manager instance.
     *
     * @var LanguageManager
     */
    protected $languages;

    /**
     * The Filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected static $appPattern = '#__\(\'(.*)\'(?:,.*)?\)#smU';

    /**
     * @var string
     */
    protected static $pattern = '#__d\(\'(?:.*)?\',.?\s?\'(.*)\'(?:,.*)?\)#smU';


    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(LanguageManager $languages, Filesystem $files)
    {
        parent::__construct();

        //
        $this->languages = $languages;

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = $this->container['config'];

        // Get the Language codes.
        $languages = array_keys(
            $config->get('languages', array())
        );

        if ($this->option('path')) {
            $path = $this->option('path');

            if (! $this->files->isDirectory($path)) {
                return $this->error('Not a directory: "' .$path .'"');
            } else if (! $this->files->isDirectory($path .DS .'Language')) {
                return $this->error('Not a translatable path: "' .$path .'"');
            }

            return $this->updateLanguageFiles($path, $languages);
        }

        // Was not specified a custom directory.
        else {
            $paths = $this->scanWorkPaths($config);
        }

        // Update the Language files in the available Domains.
        foreach ($paths as $path) {
            if (! $this->files->isDirectory($path)) {
                continue;
            } else if (! $this->files->isDirectory($path .DS .'Language')) {
                $this->comment('Not a translatable path: "' .$path .'"');

                continue;
            }

            $this->updateLanguageFiles($path, $languages);
        }
    }

    protected function scanWorkPaths(Config $config)
    {
        $result = array(
            app_path(),
            base_path('shared')
        );

        // Search for the Modules and Themes.
        $paths = array(
            $config->get('packages.modules.path', BASEPATH .'modules'),
            $config->get('packages.themes.path', BASEPATH .'themes')
        );

        foreach ($paths as $path) {
            if (! $this->files->isDirectory($path)) {
                continue;
            }

            $result = array_merge(
                $result, $this->files->glob($path .'/*', GLOB_ONLYDIR)
            );
        }

        // Search for the local Packages.
        $path = BASEPATH .'packages';

        if ($this->files->isDirectory($path)) {
            $result = array_merge($result, array_map(function ($path)
            {
                return $path .DS .'src';

            }, $this->files->glob($path .'/*', GLOB_ONLYDIR)));
        }

        return $result;
    }

    protected function updateLanguageFiles($path, $languages)
    {
        $withoutDomain = ($path == app_path());

        $pattern = $withoutDomain ? "__('" : "__d('";

        if (empty($paths = $this->fileGrep($pattern, $path))) {
            $this->comment(PHP_EOL .'No messages found in path: "' .$path .'"');

            return;
        }

        // Extract the messages from files.
        $messages = $this->extractMessages($paths, $withoutDomain);

        if (empty($messages)) {
            return;
        }

        $this->info(PHP_EOL .'Processing the messages found in path: "' .$path .'"');

        foreach ($languages as $language) {
            $this->updateLanguageFile($language, $path, array_unique($messages));
        }
    }

    protected function fileGrep($pattern, $path)
    {
        $result = array();

        $fp = opendir($path);

        while ($fileName = readdir($fp)) {
            if (preg_match("#^\.+$#", $fileName) === 1) {
                // Ignore symbolic links.
                continue;
            }

            $fullPath = $path .DS .$fileName;

            if ($this->files->isDirectory($fullPath)) {
                $result = array_merge($result, $this->fileGrep($pattern, $fullPath));
            }

            // The current path is not a directory.
            else if (preg_match("#^(.*)\.(php|tpl)$#", $fileName) !== 1) {
                continue;
            }

            // We found a PHP or TPL file.
            else if (stristr(file_get_contents($fullPath), $pattern)) {
                $result[] = $fullPath;
            }
        }

        return array_unique($result);
    }

    protected function extractMessages(array $paths, $withoutDomain)
    {
        if ($withoutDomain) {
            $pattern = '#__\(\'(.*)\'(?:,.*)?\)#smU';
        } else {
            $pattern = '#__d\(\'(?:.*)?\',.?\s?\'(.*)\'(?:,.*)?\)#smU';
        }

        $this->comment("Using PATERN: '" .$pattern."'");

        // Extract the messages from files and return them.
        $result = array();

        foreach ($paths as $path) {
            $content = $this->getFileContents($path);

            if (preg_match_all($pattern, $content, $matches) !== false) {
                if (empty($messages = $matches[1])) {
                    continue;
                }

                foreach ($messages as $message) {
                    //$message = trim($message);

                    if ($message == '$msg, $args = null') {
                        // We will skip the translation functions definition.
                        continue;
                    }

                    $result[] = str_replace("\\'", "'", $message);
                }
            }
        }

        return $result;
    }

    protected function getFileContents($path)
    {
        try {
            return $this->files->get($path);
        }
        catch (Exception $e) {
            //
        }
        catch (Throwable $e) {
            //
        }

        return '';
    }

    protected function updateLanguageFile($language, $path, array $messages)
    {
        $path = $path .str_replace('/', DS, '/Language/' .strtoupper($language) .'/messages.php');

        $data = $this->getMessagesFromFile($path);

        //
        $result = array();

        foreach ($messages as $key) {
            $result[$key] = Arr::get($data, $key, '');
        }

        $this->writeLanguageFile($path, $result);

        $this->line('Written the Language file: "' .str_replace(BASEPATH, '', $path) .'"');
    }

    protected function getMessagesFromFile($path)
    {
        $data = array();

        try {
            $data = $this->files->getRequire($path);
        }
        catch (Exception $e) {
            //
        }
        catch (Throwable $e) {
            //
        }

        return is_array($data) ? $data : array();
    }

    protected function writeLanguageFile($path, array $data)
    {
        ksort($data);

        // Make sure that the directory exists.
        $this->files->makeDirectory(dirname($path), 0755, true, true);

        // Prepare the Language file contents.
        $output = "<?php\n\nreturn " .var_export($data, true) .";\n";

        //$output = preg_replace("/^ {2}(.*)$/m","    $1", $output);

        $this->files->put($path, $output);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('path', null, InputOption::VALUE_REQUIRED, 'Indicates the path from where translation strings are processed.'),
        );
    }
}
