<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Trigger;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::introspectSchema()
     */
    protected function interceptIntrospectSchemaByTrigger(Schema $schema): array
    {
        if ($this->connection->getDatabasePlatform()->supportsTriggers()) {
            foreach ($this->listTriggers() as $trigger) {
                $schema->addTrigger($trigger);
            }
        }

        return [];
    }

    /** @return Trigger[] */
    public function listTriggers(): array
    {
        $database = $this->getDatabase(__METHOD__);

        return $this->_getPortableTriggersList(
            $this->selectTriggers($database)->fetchAllAssociative(),
        );
    }

    /** @return Trigger[] */
    protected function _getPortableTriggersList(array $triggers): array
    {
        $list = array_filter(array_map(fn($value) => $this->_getPortableTriggerDefinition($value), $triggers));
        $keys = array_map(fn($value) => strtolower($value->getQuotedName($this->platform)), $list);

        return array_combine($keys, $list);
    }

    protected function _getPortableTriggerDefinition(array $trigger): Trigger
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
        throw NotSupported::new(__METHOD__); // @codeCoverageIgnore
    }

    public function createTrigger(Trigger $trigger): void
    {
        $this->connection->executeStatement(
            $this->platform->getCreateTriggerSQL($trigger),
        );
    }

    public function dropTrigger(string $name): void
    {
        $this->connection->executeStatement(
            $this->platform->getDropTriggerSQL($name),
        );
    }
}
