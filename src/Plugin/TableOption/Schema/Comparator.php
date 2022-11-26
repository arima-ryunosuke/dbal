<?php

namespace Doctrine\DBAL\Plugin\TableOption\Schema;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::compareTables()
     */
    protected function interceptCompareTablesByTableOption(Table $oldTable, Table $newTable, TableDiff $tableDiff): array
    {
        $array_diff_assoc_recursive = function ($array1, $array2) use (&$array_diff_assoc_recursive) {
            $difference = [];
            foreach ($array1 as $key => $value) {
                if (is_array($value)) {
                    $new_diff = $array_diff_assoc_recursive($value, $array2[$key]);
                    if ($new_diff) {
                        $difference[$key] = $new_diff;
                    }
                } elseif (array_key_exists($key, $array2) && $array2[$key] != $value) {
                    $difference[$key] = $value;
                }
            }
            return $difference;
        };

        $tableDiff->changedOptions = $array_diff_assoc_recursive($newTable->getOptions(), $oldTable->getOptions());

        return [];
    }
}
