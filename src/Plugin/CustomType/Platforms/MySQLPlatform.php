<?php

namespace Doctrine\DBAL\Plugin\CustomType\Platforms;

use Doctrine\DBAL\Plugin\Interrupt;
use Doctrine\DBAL\Types\BitType;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getDefaultValueDeclarationSQL()
     */
    #[Interrupt]
    protected function getDefaultValueDeclarationSQLByCustomType(array $column): ?string
    {
        if ($this->getColumnTypeOrNull($column) instanceof BitType) {
            if (strlen($column['default'] ?? '')) {
                return ' DEFAULT ' . $column['default'];
            }
        }

        return null;
    }
}
