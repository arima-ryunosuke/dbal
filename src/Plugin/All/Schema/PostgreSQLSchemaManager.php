<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\PostgreSQLSchemaManager
 */
trait PostgreSQLSchemaManager
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Routine\Schema\PostgreSQLSchemaManager;
    use \Doctrine\DBAL\Plugin\Trigger\Schema\PostgreSQLSchemaManager;
    use \Doctrine\DBAL\Plugin\View\Schema\PostgreSQLSchemaManager;
}
