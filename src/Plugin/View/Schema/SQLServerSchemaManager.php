<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

use Doctrine\DBAL\Result;

trait SQLServerSchemaManager
{
    /**
     * @codeCoverageIgnore
     */
    protected function selectViewColumns(string $databaseName, ?string $viewName): Result
    {
        $sql = <<<SQL
                SELECT 
                    c.name                  AS name,
                    type_name(user_type_id) AS type,
                    c.max_length            AS length,
                    ~c.is_nullable          AS notnull,
                    NULL                    AS "default",
                    c.scale                 AS scale,
                    c.precision             AS precision,
                    0                       AS autoincrement,
                    c.collation_name        AS collation,
                    NULL                    AS comment
                FROM sys.columns c
                JOIN sys.views v ON v.object_id = c.object_id
                WHERE SCHEMA_NAME(v.schema_id) = SCHEMA_NAME() AND v.name = ?
            SQL;

        return $this->connection->executeQuery($sql, [$viewName]);
    }

    protected function selectViewIndexes(string $databaseName, string $viewName): Result
    {
        // @fixme
        return parent::selectViewIndexes($databaseName, $viewName); // @codeCoverageIgnore
    }
}
