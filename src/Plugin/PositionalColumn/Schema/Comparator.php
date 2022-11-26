<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::compareTables()
     */
    protected function interruptCompareTablesByPositionalColumn(Table $oldTable, Table $newTable, Column $oldColumn, Column $newColumn): ?bool
    {
        if (! $oldColumn->hasPlatformOption('beforeColumn') || ! $newColumn->hasPlatformOption('beforeColumn')) {
            return true;
        }

        $oldBefore = $oldColumn->getPlatformOption('beforeColumn');
        $newBefore = $newColumn->getPlatformOption('beforeColumn');

        if ($oldBefore === $newBefore) {
            return true;
        }

        // added column
        if (! $oldTable->hasColumn($newBefore)) {
            return true;
        }

        return null;
    }
}
