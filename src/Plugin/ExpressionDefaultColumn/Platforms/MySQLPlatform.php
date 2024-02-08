<?php

namespace Doctrine\DBAL\Plugin\ExpressionDefaultColumn\Platforms;

use Doctrine\DBAL\Plugin\Interrupt;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getDefaultValueDeclarationSQL()
     */
    #[Interrupt]
    protected function getDefaultValueDeclarationSQLByExpressionDefaultColumn(array $column): ?string
    {
        if ($column['expressionDefault'] ?? false) {
            if (strlen($column['default'] ?? '')) {
                return ' DEFAULT (' . $column['default'] . ')';
            }
        }

        return null;
    }
}
