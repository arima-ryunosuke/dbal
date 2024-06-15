<?php

namespace Doctrine\DBAL\Plugin\AutoType\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\AnyType;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnDefinition()
     */
    protected function intercept_GetPortableTableColumnDefinitionByAutoType(array $tableColumn, Column $column)
    {
        if ($column->getType() instanceof AnyType) {
            $pos = strpos($tableColumn['type'], '(');
            if ($pos !== false) {
                $column->setPlatformOption('type-declaration', substr($tableColumn['type'], $pos));
            }
        }
    }
}
