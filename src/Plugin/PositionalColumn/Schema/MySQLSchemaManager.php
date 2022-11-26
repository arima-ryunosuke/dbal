<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Schema;

use Doctrine\DBAL\Plugin\Intercept;
use Doctrine\DBAL\Schema\Column;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableColumnList()
     */
    #[Intercept]
    protected function _getPortableTableColumnListByPositionalColumn(array $list): array
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
