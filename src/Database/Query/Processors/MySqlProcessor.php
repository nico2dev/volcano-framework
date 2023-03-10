<?php

namespace Volcano\Database\Query\Processors;

use Volcano\Database\Query\Processor;


class MySqlProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return array_map(function ($result)
        {
            $result = (object) $result;

            return $result->column_name;

        }, $results);
    }
}
