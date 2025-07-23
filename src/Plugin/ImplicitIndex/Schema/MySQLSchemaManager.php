<?php

namespace Doctrine\DBAL\Plugin\ImplicitIndex\Schema;

use Doctrine\DBAL\Schema\SchemaConfig;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::createSchemaConfig()
     */
    protected function interceptCreateSchemaConfigByImplicitIndex(SchemaConfig $schemaConfig)
    {
        $schemaConfig->setExplicitForeignKeyIndexes(true);
    }
}
