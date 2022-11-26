<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Schema;

use Doctrine\DBAL\Schema\Column;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnList()
     */
    protected function intercept_GetPortableTableColumnListByPositionalColumn(array $list): array
    {
        /** @var Column[] $list */

        $before = null;
        foreach ($list as $column) {
            if ($before !== null) {
                $column->setPlatformOption('beforeColumn', $before);
            }

            $before = $column->getName();
        }

        return [];
    }
}
