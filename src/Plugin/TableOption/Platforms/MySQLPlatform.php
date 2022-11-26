<?php

namespace Doctrine\DBAL\Plugin\TableOption\Platforms;

use Doctrine\DBAL\Schema\TableDiff;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterTableSQL()
     */
    protected function interceptGetAlterTableSQLByTableOptionForOption($queryParts, TableDiff $diff)
    {
        if ($diff->changedOptions) {
            $tableOptions = [];

            if (isset($diff->changedOptions['charset'])) {
                $tableOptions[] = sprintf('DEFAULT CHARACTER SET %s', $diff->changedOptions['charset']);
            }

            if (isset($diff->changedOptions['collation'])) {
                $tableOptions[] = $this->getColumnCollationDeclarationSQL($diff->changedOptions['collation']);
            }

            if (isset($diff->changedOptions['engine'])) {
                $tableOptions[] = sprintf('ENGINE = %s', $diff->changedOptions['engine']);
            }

            if (isset($diff->changedOptions['comment'])) {
                $tableOptions[] = sprintf('COMMENT = %s', $this->quoteStringLiteral($diff->changedOptions['comment']));
            }

            if (isset($diff->changedOptions['row_format'])) {
                $tableOptions[] = sprintf('ROW_FORMAT = %s', $diff->changedOptions['row_format']);
            }

            if ($tableOptions) {
                $queryParts[] = implode(' ', $tableOptions);
            }
        }

        return ['queryParts' => $queryParts];
    }
}
