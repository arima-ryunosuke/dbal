<?php

namespace Doctrine\DBAL\Plugin\IndexOption\Platforms;

use Doctrine\DBAL\Schema\Index;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getIndexFieldDeclarationListSQL()
     */
    protected function interruptGetIndexFieldDeclarationListSQLByIndexOption(Index $index)
    {
        if ($index->hasOption('expression')) {
            return '(' . $index->getOption('expression') . ')';
        }
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getCreateIndexSQL()
     */
    protected function interceptGetCreateIndexSQLByIndexOption(string $query, Index $index)
    {
        if ($index->hasFlag('fulltext') && $index->hasOption('parser')) {
            $query .= ' WITH PARSER ' . $index->getOption('parser');
        }

        return ['query' => $query];
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getIndexDeclarationSQL()
     */
    protected function interceptGetIndexDeclarationSQLByIndexOption(string $query, Index $index)
    {
        return $this->interceptGetCreateIndexSQLByIndexOption($query, $index);
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getPreAlterTableIndexForeignKeySQL()
     */
    protected function interceptGetPreAlterTableIndexForeignKeySQLByIndexOption(string $query, Index $addedIndex)
    {
        return $this->interceptGetCreateIndexSQLByIndexOption($query, $addedIndex);
    }
}
