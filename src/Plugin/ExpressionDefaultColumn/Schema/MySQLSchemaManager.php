<?php

namespace Doctrine\DBAL\Plugin\ExpressionDefaultColumn\Schema;

use Doctrine\DBAL\Schema\Column;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnDefinition()
     */
    protected function intercept_GetPortableTableColumnDefinitionByExpressionDefaultColumn(array $tableColumn, Column $column)
    {
        // CurrentTimestamp and GenerationExpression are also a kind of DEFAULT_GENERATED but different from the expression default since mysql8.0.13
        if (strpos($tableColumn['extra'], 'DEFAULT_GENERATED') !== false
            && $tableColumn['default'] !== $this->_platform->getCurrentTimestampSQL()
            && ! strlen($tableColumn['generationexpression'] ?? '')
        ) {
            // information_schema.COLUMNS.COLUMN_DEFAULT is quoted string
            $column->setDefault(stripslashes($column->getDefault() ?? ''));
            $column->setPlatformOption('expressionDefault', true);
        }
    }
}
