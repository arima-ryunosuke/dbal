<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\Schema
 */
trait Schema
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Event\Schema\Schema;
    use \Doctrine\DBAL\Plugin\Routine\Schema\Schema;
    use \Doctrine\DBAL\Plugin\Trigger\Schema\Schema;
    use \Doctrine\DBAL\Plugin\Utility\Schema\Schema;
    use \Doctrine\DBAL\Plugin\View\Schema\Schema;
}
