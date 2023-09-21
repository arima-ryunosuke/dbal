<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\SQLiteSchemaManager
 */
trait SQLiteSchemaManager
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Trigger\Schema\SQLiteSchemaManager;
    use \Doctrine\DBAL\Plugin\View\Schema\SQLiteSchemaManager;
}
