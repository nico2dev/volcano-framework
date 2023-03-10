<?php

namespace Volcano\Pagination;

use Volcano\Support\Str;


class UrlGenerator
{
    /**
     * The Paginator implementation.
     *
     * @var \Volcano\Pagination\PaginatorInterface
     */
    protected $paginator;


    /**
     * Create a new URL Generator instance.
     *
     * @param  \Volcano\Pagination\PaginatorInterface  $paginator
     * @return void
     */
    public function __construct(AbstractPaginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Resolve the URL for a given page number.
     *
     * @param  int  $page
     * @return string
     */
    public function url($page)
    {
        $paginator = (object) $this->getPaginator();

        //
        $pageName = $paginator->getPageName();

        $query = array_merge(
            $paginator->getQuery(), array($pageName => $page)
        );

        return $this->buildUrl(
            $paginator->getPath(), $query, $paginator->fragment()
        );
    }

    /**
     * Build the full query portion of a URL.
     *
     * @param  string  $path
     * @param  array  $query
     * @param  string|null  $fragment
     * @return string
     */
    protected function buildUrl($path, array $query, $fragment)
    {
        if (! empty($query)) {
            $separator = Str::contains($path, '?') ? '&' : '?';

            $path .= $separator .http_build_query($query, '', '&');
        }

        if (! empty($fragment)) {
            $path .= '#' .$fragment;
        }

        return $path;
    }

    /**
     * Get the Paginator implementation.
     *
     * @return string
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
