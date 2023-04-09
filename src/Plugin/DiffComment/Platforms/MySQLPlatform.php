<?php

namespace Doctrine\DBAL\Plugin\DiffComment\Platforms;

use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\TableDiff;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterTableSQL()
     */
    protected function interceptGetAlterTableSQLByDiffCommentForColumn(ColumnDiff $columnDiff, array $queryParts): array
    {
        $key              = array_key_last($queryParts);
        $queryParts[$key] = $this->buildDiffComment($columnDiff->getDiff()) . $queryParts[$key];

        return ['queryParts' => $queryParts];
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterTableSQL()
     */
    protected function interceptGetAlterTableSQLByDiffCommentForTable(TableDiff $diff, array $tableSql): array
    {
        $key            = array_key_last($tableSql);
        $tableSql[$key] = $this->buildDiffComment($diff->getDiff()) . $tableSql[$key];

        return ['tableSql' => $tableSql];
    }
}
