<?php

namespace Doctrine\DBAL\Plugin\ExpressionDefaultColumn\Platforms;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getDefaultValueDeclarationSQL()
     */
    protected function interruptGetDefaultValueDeclarationSQLByExpressionDefaultColumn(array $column): ?string
    {
        if ($column['expressionDefault'] ?? false) {
            if (strlen($column['default'] ?? '')) {
                return ' DEFAULT (' . $column['default'] . ')';
            }
        }

        return null;
    }
}
