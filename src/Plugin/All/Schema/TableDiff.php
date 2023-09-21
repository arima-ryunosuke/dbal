<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\TableDiff
 */
trait TableDiff
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\DiffComment\Schema\TableDiff;
    use \Doctrine\DBAL\Plugin\TableOption\Schema\TableDiff;
}
