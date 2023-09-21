<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\Comparator
 */
trait Comparator
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Event\Schema\Comparator;
    use \Doctrine\DBAL\Plugin\IndexOption\Schema\Comparator;
    use \Doctrine\DBAL\Plugin\PositionalColumn\Schema\Comparator;
    use \Doctrine\DBAL\Plugin\Routine\Schema\Comparator;
    use \Doctrine\DBAL\Plugin\TableOption\Schema\Comparator;
    use \Doctrine\DBAL\Plugin\Trigger\Schema\Comparator;
    use \Doctrine\DBAL\Plugin\View\Schema\Comparator;
}
