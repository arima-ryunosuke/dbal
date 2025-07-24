<?php

namespace Doctrine\DBAL\Plugin\ImplicitIndex\Schema;

use Doctrine\DBAL\Plugin\Intercept;
use Doctrine\DBAL\Schema\SchemaConfig;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::createSchemaConfig()
     */
    #[Intercept]
    protected function createSchemaConfigByImplicitIndex(SchemaConfig $schemaConfig): array
    {
        $schemaConfig->setExplicitForeignKeyIndexes(true);

        return [];
    }
}
