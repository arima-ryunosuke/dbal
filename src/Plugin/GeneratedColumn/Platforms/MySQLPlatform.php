<?php

namespace Doctrine\DBAL\Plugin\GeneratedColumn\Platforms;

use Doctrine\DBAL\Plugin\Interrupt;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getColumnDeclarationSQL()
     */
    #[Interrupt]
    protected function getColumnDeclarationSQLByGeneratedColumnByGeneratedColumn(array $column): ?string
    {
        if (! empty($column['generation'])) {
            $typeDecl = $this->getColumnType($column)->getSQLDeclaration($column, $this);
            $notnull  = ! empty($column['notnull']) ? ' NOT NULL' : '';
            return $typeDecl . ' AS (' . $column['generation']['expression'] . ') ' . $column['generation']['type'] . $notnull;
        }

        return null;
    }
}
