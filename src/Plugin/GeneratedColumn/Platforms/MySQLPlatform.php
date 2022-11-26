<?php

namespace Doctrine\DBAL\Plugin\GeneratedColumn\Platforms;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getColumnDeclarationSQL()
     */
    protected function interruptGetColumnDeclarationSQLByGeneratedColumn($column)
    {
        if (! empty($column['generation'])) {
            $typeDecl = $column['type']->getSQLDeclaration($column, $this);
            $notnull  = ! empty($column['notnull']) ? ' NOT NULL' : '';
            return $typeDecl . ' AS (' . $column['generation']['expression'] . ') ' . $column['generation']['type'] . $notnull;
        }

        return null;
    }
}
