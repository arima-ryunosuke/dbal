<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Trigger;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::createSchema()
     */
    protected function interceptIntrospectSchemaByTrigger(Schema $schema)
    {
        if ($this->_conn->getDatabasePlatform()->supportsTriggers()) {
            foreach ($this->listTriggers() as $trigger) {
                $schema->addTrigger($trigger);
            }
        }
    }

    public function listTriggers()
    {
        $database = $this->getDatabase(__METHOD__);

        return $this->_getPortableTriggersList(
            $this->selectTriggers($database)->fetchAllAssociative(),
        );
    }

    protected function _getPortableTriggersList($triggers)
    {
        $list = array_filter(array_map(fn($value) => $this->_getPortableTriggerDefinition($value), $triggers));
        $keys = array_map(fn($value) => strtolower($value->getQuotedName($this->_platform)), $list);

        return array_combine($keys, $list);
    }

    protected function _getPortableTriggerDefinition($trigger)
    {
        assert(isset($trigger['name'], $trigger['statement'], $trigger['tableName']));
        $name      = $trigger['name'];
        $statement = $trigger['statement'];
        $tableName = $trigger['tableName'];
        unset($trigger['name'], $trigger['statement'], $trigger['tableName']);

        return new Trigger($name, $statement, $tableName, $trigger);
    }

    protected function selectTriggers(string $databaseName): Result
    {
        throw Exception::notSupported(__METHOD__); // @codeCoverageIgnore
    }

    public function createTrigger(Trigger $trigger)
    {
        $this->_conn->executeStatement(
            $this->_platform->getCreateTriggerSQL($trigger),
        );
    }

    public function dropTrigger($name)
    {
        $this->_conn->executeStatement(
            $this->_platform->getDropTriggerSQL($name),
        );
    }
}
