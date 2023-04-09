<?php

namespace Doctrine\DBAL\Plugin\DiffComment\Platforms;

use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\TableDiff;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterTableSQL()
     */
    protected function interceptGetAlterTableSQLByDiffCommentForColumn(ColumnDiff $columnDiff, $queryParts)
    {
        $key              = array_key_last($queryParts);
        $queryParts[$key] = $this->buildDiffComment($columnDiff->getDiff()) . $queryParts[$key];

        return ['queryParts' => $queryParts];
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterTableSQL()
     */
    protected function interceptGetAlterTableSQLByDiffCommentForTable(TableDiff $diff, $sql)
    {
        $key       = array_key_last($sql);
        $sql[$key] = $this->buildDiffComment($diff->getDiff()) . $sql[$key];

        return ['sql' => $sql];
    }
}
