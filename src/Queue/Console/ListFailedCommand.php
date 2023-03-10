<?php

namespace Volcano\Queue\Console;

use Volcano\Console\Command;


class ListFailedCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queue:failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all of the failed queue jobs';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $rows = array();

        foreach ($this->container['queue.failer']->all() as $failed)
        {
            $rows[] = $this->parseFailedJob((array) $failed);
        }

        if (count($rows) == 0)
        {
            return $this->info('No failed jobs!');
        }

        $this->table(array('ID', 'Connection', 'Queue', 'Class', 'Failed At'), $rows);
    }

    /**
     * Parse the failed job row.
     *
     * @param  array  $failed
     * @return array
     */
    protected function parseFailedJob(array $failed)
    {
        $row = array_values(array_except($failed, array('payload')));

        array_splice($row, 3, 0, array_get(json_decode($failed['payload'], true), 'job'));

        return $row;
    }

}
