<?php

namespace Doctrine\DBAL\Plugin\AutoType\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\AnyType;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnDefinition()
     */
    protected function intercept_GetPortableTableColumnDefinitionByAutoType(array $tableColumn, Column $column): array
    {
        if ($column->getType() instanceof AnyType) {
            $column->setPlatformOption('type-declaration', $tableColumn['column_type']);
        }

        return [];
    }
}
