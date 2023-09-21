<?php

namespace Doctrine\DBAL\Plugin\All\Schema\ForeignKeyConstraint;

/**
 * @used-by \Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction
 */
trait ReferentialAction
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Utility\Schema\ForeignKeyConstraint\ReferentialAction;
}
