<?php

namespace Doctrine\DBAL\Plugin\CustomType\Platforms;

use Doctrine\DBAL\Types\BitType;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getDefaultValueDeclarationSQL()
     */
    protected function interruptGetDefaultValueDeclarationSQLByCustomType($column)
    {
        if ($column['type'] instanceof BitType) {
            if (strlen($column['default'] ?? '')) {
                return ' DEFAULT ' . $column['default'];
            }
        }
    }
}
