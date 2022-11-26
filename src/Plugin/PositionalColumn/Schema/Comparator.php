<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::compareTables()
     */
    protected function interceptCompareTablesByPositionalColumn(Table $toTable, TableDiff $tableDiff)
    {
        $changedColumns = $tableDiff->changedColumns;

        $sortedByToColumns = [];
        foreach ($toTable->getColumns() as $toColumnName => $toColumn) {
            if (array_key_exists($toColumnName, $changedColumns)) {
                $sortedByToColumns[$toColumnName] = $changedColumns[$toColumnName];
            }
        }

        $tableDiff->changedColumns = $sortedByToColumns;
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::compareTables()
     */
    protected function interruptCompareTablesByPositionalColumn(Table $fromTable, Table $toTable, Column $column, Column $toColumn)
    {
        $fromColumn = $column;

        if (! $fromColumn->hasPlatformOption('beforeColumn') || ! $toColumn->hasPlatformOption('beforeColumn')) {
            return true;
        }

        $fromBefore = $fromColumn->getPlatformOption('beforeColumn');
        $toBefore   = $toColumn->getPlatformOption('beforeColumn');

        if ($fromBefore === $toBefore) {
            return true;
        }

        // added column
        if (! $fromTable->hasColumn($toBefore)) {
            return true;
        }

        return false;
    }
}
