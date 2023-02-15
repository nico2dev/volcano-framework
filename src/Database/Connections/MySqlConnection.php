<?php

namespace Volcano\Database\Connections;

use Volcano\Database\Connection;
use Volcano\Database\Schema\MySqlBuilder;

use Volcano\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Volcano\Database\Query\Processors\MySqlProcessor as QueryProcessor;
use Volcano\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;

use Doctrine\DBAL\Driver\PDOMySql\Driver as DoctrineDriver;


class MySqlConnection extends Connection
{

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Volcano\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new MySqlBuilder($this);
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Volcano\Database\Query\Grammars\MySqlGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Volcano\Database\Schema\Grammars\MySqlGrammar
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
        return new QueryProcessor;
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOMySql\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
}
