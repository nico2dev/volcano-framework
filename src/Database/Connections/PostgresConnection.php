<?php

namespace Volcano\Database\Connections;

use Volcano\Database\Connection;
use Volcano\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Volcano\Database\Query\Processors\PostgresProcessor as QueryProcessor;
use Volcano\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;

use Doctrine\DBAL\Driver\PDOPgSql\Driver as DoctrineDriver;


class PostgresConnection extends Connection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \Volcano\Database\Query\Grammars\PostgresGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar());
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Volcano\Database\Schema\Grammars\PostgresGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Volcano\Database\Query\Processors\PostgresProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new QueryProcessor();
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOPgSql\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
}
