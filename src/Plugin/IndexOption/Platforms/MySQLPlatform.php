<?php

namespace Doctrine\DBAL\Plugin\IndexOption\Platforms;

use Doctrine\DBAL\Plugin\Intercept;
use Doctrine\DBAL\Schema\Index;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getCreateIndexSQL()
     */
    #[Intercept]
    protected function getCreateIndexSQLByIndexOption(string $query, Index $index): array
    {
        if ($index->hasFlag('fulltext') && $index->hasOption('parser')) {
            $query .= ' WITH PARSER ' . $index->getOption('parser');
        }

        return ['query' => $query];
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getIndexDeclarationSQL()
     */
    #[Intercept]
    protected function getIndexDeclarationSQLByIndexOption(string $query, Index $index): array
    {
        return $this->getCreateIndexSQLByIndexOption($query, $index);
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getPreAlterTableIndexForeignKeySQL()
     */
    #[Intercept]
    protected function getPreAlterTableIndexForeignKeySQLByIndexOption(string $query, Index $addedIndex): array
    {
        return $this->getCreateIndexSQLByIndexOption($query, $addedIndex);
    }
}
