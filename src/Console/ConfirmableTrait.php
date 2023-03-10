<?php

namespace Volcano\Console;

use Closure;


trait ConfirmableTrait
{
    /**
     * Confirm before proceeding with the action
     *
     * @param  string    $warning
     * @param  \Closure  $callback
     * @return bool
     */
    public function confirmToProceed($warning = 'Application In Production!', Closure $callback = null)
    {
        $shouldConfirm = $callback ?: $this->getDefaultConfirmCallback();

        if (call_user_func($shouldConfirm))
        {
            if ($this->option('force')) return true;

            $this->comment(str_repeat('*', strlen($warning) + 12));
            $this->comment('*     '.$warning.'     *');
            $this->comment(str_repeat('*', strlen($warning) + 12));
            $this->output->writeln('');

            $confirmed = $this->confirm('Do you really wish to run this command?');

            if (! $confirmed) {
                $this->comment('Command Cancelled!');

                return false;
            }
        }

        return true;
    }

    /**
     * Get the default confirmation callback.
     *
     * @return \Closure
     */
    protected function getDefaultConfirmCallback()
    {
        return function() { return $this->getVolcano()->environment() == 'production'; };
    }

}
