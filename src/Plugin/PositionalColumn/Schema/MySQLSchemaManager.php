<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaConfig;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::createSchemaConfig()
     */
    protected function interceptCreateSchemaConfigByPositionalColumn(SchemaConfig $schemaConfig)
    {
        $schemaConfig->setPositionalColumn(true);
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnList()
     */
    protected function intercept_GetPortableTableColumnListByPositionalColumn($list)
    {
        /** @var Column[] $list */

        $before = null;
        foreach ($list as $column) {
            if ($before !== null) {
                $column->setPlatformOption('beforeColumn', $before);
            }

            $before = $column->getName();
        }
    }
}
