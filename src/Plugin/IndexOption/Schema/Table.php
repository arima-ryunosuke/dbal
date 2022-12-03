<?php

namespace Doctrine\DBAL\Plugin\IndexOption\Schema;

use Doctrine\DBAL\Schema\Index;

trait Table
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Table::_addForeignKeyConstraint()
     */
    protected function intercept_AddForeignKeyConstraintByPositionalColumn(Index $indexCandidate)
    {
        $indexCandidate->addFlag('IMPLICIT');
    }
}
