<?php

namespace Doctrine\DBAL\Plugin\CustomType\Platforms;

use Doctrine\DBAL\Types\BitType;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getDefaultValueDeclarationSQL()
     */
    protected function interruptGetDefaultValueDeclarationSQLByCustomType(array $column): ?string
    {
        if ($column['type'] instanceof BitType) {
            if (strlen($column['default'] ?? '')) {
                return ' DEFAULT ' . $column['default'];
            }
        }

        return null;
    }
}
