<?php

namespace Doctrine\DBAL\Platforms;

/**
 * Provides the behavior, features and SQL dialect of the MySQL 5.6 database platform.
 */
class MySQL56Platform extends MySqlPlatform
{
    /**
     * {@inheritDoc}
     */
    public function getDateTimeTypeDeclarationSQL(array $fieldDeclaration)
    {
        $declaration = parent::getDateTimeTypeDeclarationSQL($fieldDeclaration);

        if (isset($fieldDeclaration['length']) && $fieldDeclaration['length']) {
            $declaration .= '(' . $fieldDeclaration['length'] . ')';
        }

        return $declaration;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeTypeDeclarationSQL(array $fieldDeclaration)
    {
        $declaration = parent::getTimeTypeDeclarationSQL($fieldDeclaration);

        if (isset($fieldDeclaration['length']) && $fieldDeclaration['length']) {
            $declaration .= '(' . $fieldDeclaration['length'] . ')';
        }

        return $declaration;
    }
}
