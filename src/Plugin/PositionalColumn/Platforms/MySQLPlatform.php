<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Platforms;

use Doctrine\DBAL\Plugin\Intercept;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getColumnDeclarationSQL()
     */
    #[Intercept]
    protected function getColumnDeclarationSQLByPositionalColumn(string $declaration, array $column, string $name): array
    {
        // empty name is compare context
        if ($name === '') {
            return [];
        }

        if (array_key_exists('beforeColumn', $column)) {
            if ($column['beforeColumn']) {
                $declaration .= " AFTER " . $column['beforeColumn'];
            } else {
                $declaration .= " FIRST";
            }
        }

        return ['declaration' => $declaration];
    }

    public function getColumnDeclarationListSQL(array $columns): string
    {
        // premise: this method is called when create table context only

        foreach ($columns as &$column) {
            unset($column['beforeColumn']);
        }

        return parent::getColumnDeclarationListSQL($columns);
    }
}
