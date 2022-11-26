<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Result;

trait MySQLSchemaManager
{
    protected function selectTriggers(string $databaseName): Result
    {
        $sql = <<<SQL
            SELECT
                TRIGGER_NAME       AS `name`,
                EVENT_MANIPULATION AS `event`,
                EVENT_OBJECT_TABLE AS `tableName`,
                ACTION_STATEMENT   AS `statement`,
                ACTION_TIMING      AS `timing`,
                DEFINER            AS `definer`
            FROM information_schema.TRIGGERS
            WHERE TRIGGER_SCHEMA = ?
            ORDER BY TRIGGER_NAME
        SQL;

        return $this->connection->executeQuery($sql, [$databaseName]);
    }
}
