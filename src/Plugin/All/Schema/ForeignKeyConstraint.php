<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\ForeignKeyConstraint
 */
trait ForeignKeyConstraint
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\ForeignKeyConstraintOption\Schema\ForeignKeyConstraint;
}
