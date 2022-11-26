<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Plugin\Trigger\Schema\Exception\TriggerDoesNotExist;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Trigger;

/**
 * @codeCoverageIgnore this is implemented to broaden one's horizons
 */
trait PostgreSQLSchemaManager
{
    protected function _getPortableTriggerDefinition(array $trigger): Trigger
    {
        assert(isset($trigger['name'], $trigger['statement'], $trigger['tableName']));
        $name        = $trigger['name'];
        $schema_name = $trigger['schema_name'];
        $statement   = $trigger['statement'];
        $tableName   = $trigger['tableName'];
        unset($trigger['name'], $trigger['schema_name'], $trigger['statement'], $trigger['tableName']);

        return new Trigger("$schema_name.$name", $statement, $tableName, $trigger);
    }

    protected function selectTriggers(string $databaseName): Result
    {
        $sql = <<<SQL
            SELECT
                trigger_name       AS "name",
                trigger_schema     AS "schema_name",
                event_manipulation AS "event",
                event_object_table AS "tableName",
                action_statement   AS "statement",
                action_timing      AS "timing",
                action_orientation AS "orientation"
            FROM information_schema.TRIGGERS t
            WHERE t.trigger_catalog = ?
                AND t.trigger_schema NOT LIKE 'pg\_%'
                AND t.trigger_schema != 'information_schema'
            ORDER BY trigger_name
        SQL;

        return $this->connection->executeQuery($sql, [$databaseName]);
    }

    public function dropTrigger(string $name): void
    {
        $matches = array_filter($this->listTriggers(), function (Trigger $trigger) use ($name) {
            if ($trigger->getLocalName() === $name) {
                return true;
            }
        });
        $trigger = reset($matches) ?: null; //throw TriggerDoesNotExist::new($name);
        if (! $trigger) {
            throw TriggerDoesNotExist::new($name);
        }
        $this->connection->executeStatement(
            $this->platform->getDropTriggerSQL($name, $trigger->getTableName()),
        );
    }
}
