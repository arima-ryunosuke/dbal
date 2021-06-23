<?php

namespace Doctrine\DBAL\Platforms;

/**
 * Provides the behavior, features and SQL dialect of the MySQL 5.6 (5.6.4 GA) database platform.
 */
class MySQL56Platform extends MySQLPlatform
{
    /**
     * {@inheritDoc}
     */
    public function getDateTimeTypeDeclarationSQL(array $column)
    {
        $declaration = parent::getDateTimeTypeDeclarationSQL($column);

        if (isset($column['length']) && $column['length']) {
            $declaration .= '(' . $column['length'] . ')';
        }

        return $declaration;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeTypeDeclarationSQL(array $column)
    {
        $declaration = parent::getTimeTypeDeclarationSQL($column);

        if (isset($column['length']) && $column['length']) {
            $declaration .= '(' . $column['length'] . ')';
        }

        return $declaration;
    }
}
