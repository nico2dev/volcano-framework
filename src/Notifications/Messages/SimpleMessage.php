<?php

namespace Volcano\Notifications\Messages;

use Volcano\Notifications\Action;


class SimpleMessage
{
    /**
     * The "level" of the notification (info, success, error).
     *
     * @var string
     */
    public $level = 'info';

    /**
     * The subject of the notification.
     *
     * @var string
     */
    public $subject;

    /**
     * The notification's greeting.
     *
     * @var string
     */
    public $greeting;

    /**
     * The "intro" lines of the notification.
     *
     * @var array
     */
    public $introLines = array();

    /**
     * The "outro" lines of the notification.
     *
     * @var array
     */
    public $outroLines = array();

    /**
     * The text / label for the action.
     *
     * @var string
     */
    public $actionText;

    /**
     * The action URL.
     *
     * @var string
     */
    public $actionUrl;


    /**
     * Indicate that the notification gives information about a successful operation.
     *
     * @return $this
     */
    public function success()
    {
        $this->level = 'success';

        return $this;
    }

    /**
     * Indicate that the notification gives information about an error.
     *
     * @return $this
     */
    public function error()
    {
        $this->level = 'error';

        return $this;
    }

    /**
     * Set the "level" of the notification (success, error, etc.).
     *
     * @param  string  $level
     * @return $this
     */
    public function level($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Set the subject of the notification.
     *
     * @param  string  $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the greeting of the notification.
     *
     * @param  string  $greeting
     * @return $this
     */
    public function greeting($greeting)
    {
        $this->greeting = $greeting;

        return $this;
    }

    /**
     * Add a line of text to the notification.
     *
     * @param  \Volcano\Notifications\Action|string  $line
     * @return $this
     */
    public function line($line)
    {
        return $this->with($line);
    }

    /**
     * Add a line of text to the notification.
     *
     * @param  \Volcano\Notifications\Action|string|array  $line
     * @return $this
     */
    public function with($line)
    {
        if ($line instanceof Action) {
            $this->action($line->text, $line->url);
        } else if (is_null($this->actionText)) {
            $this->introLines[] = $this->format($line);
        } else {
            $this->outroLines[] = $this->format($line);
        }

        return $this;
    }

    /**
     * Format the given line of text.
     *
     * @param  string|array  $line
     * @return string
     */
    protected function format($line)
    {
        if (is_array($line)) {
            return implode(' ', array_map('trim', $line));
        }

        $lines = preg_split('/\\r\\n|\\r|\\n/', $line);

        return trim(implode(' ', array_map('trim', $lines)));
    }

    /**
     * Configure the "call to action" button.
     *
     * @param  string  $text
     * @param  string  $url
     * @return $this
     */
    public function action($text, $url)
    {
        $this->actionText = $text;

        $this->actionUrl = $url;

        return $this;
    }

    /**
     * Get an array representation of the message.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'level'       => $this->level,
            'subject'     => $this->subject,
            'greeting'    => $this->greeting,
            'introLines'  => $this->introLines,
            'outroLines'  => $this->outroLines,
            'actionText'  => $this->actionText,
            'actionUrl'   => $this->actionUrl,
        );
    }
}
