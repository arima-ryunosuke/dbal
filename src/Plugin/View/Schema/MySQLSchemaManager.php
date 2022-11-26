<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\View;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableViewDefinition()
     */
    protected function intercept_GetPortableViewDefinitionByView(View $viewObject, array $view): array
    {
        $viewObject->addOption('checkOption', $view['CHECK_OPTION']);
        $viewObject->addOption('updatable', filter_var($view['IS_UPDATABLE'], FILTER_VALIDATE_BOOLEAN));

        return [];
    }

    /**
     * @codeCoverageIgnore
     * @see self::selectTableColumns()
     */
    protected function selectViewColumns(string $databaseName, ?string $viewName): Result
    {
        $columnTypeSQL = $this->platform->getColumnTypeSQLSnippet('c', $databaseName);

        $sql = 'SELECT';

        if ($viewName === null) {
            $sql .= ' c.TABLE_NAME,';
        }

        $sql .= <<<SQL
       c.COLUMN_NAME        AS field,
       $columnTypeSQL       AS type,
       c.COLUMN_TYPE,
       c.CHARACTER_MAXIMUM_LENGTH,
       c.CHARACTER_OCTET_LENGTH,
       c.NUMERIC_PRECISION,
       c.NUMERIC_SCALE,
       c.IS_NULLABLE        AS `null`,
       c.COLUMN_KEY         AS `key`,
       c.COLUMN_DEFAULT     AS `default`,
       c.EXTRA,
       c.COLUMN_COMMENT     AS comment,
       c.CHARACTER_SET_NAME AS characterset,
       c.COLLATION_NAME     AS collation
FROM information_schema.COLUMNS c
    INNER JOIN information_schema.TABLES t
        ON t.TABLE_NAME = c.TABLE_NAME
SQL;

        // The schema name is passed multiple times as a literal in the WHERE clause instead of using a JOIN condition
        // in order to avoid performance issues on MySQL older than 8.0 and the corresponding MariaDB versions
        // caused by https://bugs.mysql.com/bug.php?id=81347
        $conditions = ['c.TABLE_SCHEMA = ?', 't.TABLE_SCHEMA = ?', "t.TABLE_TYPE = 'VIEW'"];
        $params     = [$databaseName, $databaseName];

        if ($viewName !== null) {
            $conditions[] = 't.TABLE_NAME = ?';
            $params[]     = $viewName;
        }

        $sql .= ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY ORDINAL_POSITION';

        return $this->connection->executeQuery($sql, $params);
    }
}
