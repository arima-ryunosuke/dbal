<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Platforms;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getColumnDeclarationSQL()
     */
    protected function interceptGetColumnDeclarationSQLByPositionalColumn(string $declaration, array $column, string $name): array
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
