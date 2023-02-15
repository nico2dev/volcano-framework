<?php

namespace Volcano\Database\Connections;

use Volcano\Database\Connection;

use Volcano\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;
use Volcano\Database\Query\Processors\SQLiteProcessor as QueryProcessor;
use Volcano\Database\Schema\Grammars\SQLiteGrammar as SchemaGrammar;

use Doctrine\DBAL\Driver\PDOSqlite\Driver as DoctrineDriver;


class SQLiteConnection extends Connection
{

    /**
     * Get the default query grammar instance.
     *
     * @return \Volcano\Database\Query\Grammars\SQLiteGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar());
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Volcano\Database\Schema\Grammars\SQLiteGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Volcano\Database\Query\Processors\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new QueryProcessor();
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOSqlite\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
}
