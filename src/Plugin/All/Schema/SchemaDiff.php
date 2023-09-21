<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\SchemaDiff
 */
trait SchemaDiff
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Event\Schema\SchemaDiff;
    use \Doctrine\DBAL\Plugin\Routine\Schema\SchemaDiff;
    use \Doctrine\DBAL\Plugin\Trigger\Schema\SchemaDiff;
    use \Doctrine\DBAL\Plugin\View\Schema\SchemaDiff;
}
