<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager
 */
trait AbstractSchemaManager
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Event\Schema\AbstractSchemaManager;
    use \Doctrine\DBAL\Plugin\Routine\Schema\AbstractSchemaManager;
    use \Doctrine\DBAL\Plugin\Trigger\Schema\AbstractSchemaManager;
    use \Doctrine\DBAL\Plugin\View\Schema\AbstractSchemaManager;
}
