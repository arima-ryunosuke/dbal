<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

use Doctrine\DBAL\Result;

trait SQLiteSchemaManager
{
    /**
     * @codeCoverageIgnore
     * @see self::selectTableColumns()
     */
    protected function selectViewColumns(string $databaseName, ?string $viewName): Result
    {
        $sql = <<<'SQL'
            SELECT t.name AS table_name,
                   c.*,
                   CASE c.type
                       WHEN '' THEN 'string'
                       ELSE c.type
                   END AS type
              FROM sqlite_master t
              JOIN pragma_table_info(t.name) c
SQL;

        $conditions = [
            "t.type = 'view'",
            "t.name NOT IN ('geometry_columns', 'spatial_ref_sys', 'sqlite_sequence')",
        ];
        $params     = [];

        if ($viewName !== null) {
            $conditions[] = 't.name = ?';
            $params[]     = str_replace('.', '__', $viewName);
        }

        $sql .= ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY t.name, c.cid';

        return $this->connection->executeQuery($sql, $params);
    }
}
