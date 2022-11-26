<?php

namespace Doctrine\DBAL\Plugin\GeneratedColumn\Schema;

use Doctrine\DBAL\Schema\Column;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::selectTableColumns()
     */
    protected function interceptSelectTableColumnsByGeneratedColumn(string $sql)
    {
        $sql = strtr($sql, [
            'FROM information_schema' => ",c.GENERATION_EXPRESSION AS generationexpression\nFROM information_schema",
        ]);

        return ['sql' => $sql];
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnDefinition()
     */
    protected function intercept_GetPortableTableColumnDefinitionByGeneratedColumn(array $tableColumn, Column $column)
    {
        // no check "extra" key. because it contains other "GENERATED" keyword. e.g. "DEFAULT_GENERATED" on DATETIME
        if (strlen($tableColumn['generationexpression'] ?? '')) {
            $column->setPlatformOption('generation', [
                'type'       => strstr($tableColumn['extra'], ' ', true),
                'expression' => stripslashes($tableColumn['generationexpression']),
            ]);
        }
    }
}
