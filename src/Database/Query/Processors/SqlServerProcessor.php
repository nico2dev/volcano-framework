<?php

namespace Volcano\Database\Query\Processors;

use Volcano\Database\Query\Builder;
use Volcano\Database\Query\Processor;


class SqlServerProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return array_values(array_map(function ($result)
        {
            return $result->name;

        }, $results));
    }
}
