<?php

namespace Doctrine\DBAL\Plugin\ExpressionDefaultColumn\Schema;

use Doctrine\DBAL\Schema\Column;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnDefinition()
     */
    protected function intercept_GetPortableTableColumnDefinitionByExpressionDefaultColumn(array $tableColumn, Column $column): array
    {
        // CurrentTimestamp and GenerationExpression are also a kind of DEFAULT_GENERATED but different from the expression default since mysql8.0.13
        if (str_contains($tableColumn['extra'], 'DEFAULT_GENERATED')
            && $tableColumn['default'] !== $this->platform->getCurrentTimestampSQL()
            && ! strlen($tableColumn['generationexpression'] ?? '')
        ) {
            // information_schema.COLUMNS.COLUMN_DEFAULT is quoted string
            $column->setDefault(stripslashes($column->getDefault() ?? ''));
            $column->setPlatformOption('expressionDefault', true);
        }

        return [];
    }
}
