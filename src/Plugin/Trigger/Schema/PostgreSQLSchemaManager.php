<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Plugin\Trigger\Schema\Exception\TriggerDoesNotExist;
use Doctrine\DBAL\Result;

/**
 * @codeCoverageIgnore this is implemented to broaden one's horizons
 */
trait PostgreSQLSchemaManager
{
    protected function selectTriggers(string $databaseName): Result
    {
        $sql = <<<SQL
            SELECT
                trigger_name       AS "name",
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
        $trigger = $this->listTriggers()[$name] ?? null; //throw TriggerDoesNotExist::new($name);
        if (! $trigger) {
            throw TriggerDoesNotExist::new($name);
        }
        $this->connection->executeStatement(
            $this->platform->getDropTriggerSQL($name, $trigger->getTableName()),
        );
    }
}
