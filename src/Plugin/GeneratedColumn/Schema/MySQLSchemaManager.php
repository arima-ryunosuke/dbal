<?php

namespace Doctrine\DBAL\Plugin\GeneratedColumn\Schema;

use Doctrine\DBAL\Plugin\Intercept;
use Doctrine\DBAL\Schema\Column;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::selectTableColumns()
     */
    #[Intercept]
    protected function selectTableColumnsByGeneratedColumn(string $sql): array
    {
        $sql = strtr($sql, [
            'FROM information_schema' => ",c.GENERATION_EXPRESSION AS generationexpression\nFROM information_schema",
        ]);

        return ['sql' => $sql];
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnDefinition()
     */
    #[Intercept]
    protected function _getPortableTableColumnDefinitionByGeneratedColumn(array $tableColumn, Column $column): array
    {
        // no check "extra" key. because it contains other "GENERATED" keyword. e.g. "DEFAULT_GENERATED" on DATETIME
        if (strlen($tableColumn['generationexpression'] ?? '')) {
            $column->setPlatformOption('generation', [
                'type'       => strstr($tableColumn['extra'], ' ', true),
                'expression' => stripslashes($tableColumn['generationexpression']),
            ]);
        }

        return [];
    }
}
