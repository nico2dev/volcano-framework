<?php

namespace Volcano\View;

use Volcano\Contracts\HtmlableInterface;


class Expression implements HtmlableInterface
{
    /**
     * The HTML string.
     *
     * @var string
     */
    protected $html;


    /**
     * Create a new HTML string instance.
     *
     * @param  string  $html
     * @return void
     */
    public function __construct($html)
    {
        $this->html = $html;
    }

    /**
     * Get the the HTML string.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->html;
    }

    /**
     * Get the the HTML string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }
}
