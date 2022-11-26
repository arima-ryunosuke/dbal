<?php

namespace Doctrine\DBAL\Plugin\TableOption\Schema;

use Doctrine\DBAL\Plugin\Intercept;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::fetchTableOptionsByTable()
     */
    #[Intercept]
    protected function fetchTableOptionsByTableByTableByTableOptionForSql(string $sql): array
    {
        $sql = strtr($sql, [
            'FROM information_schema' => ",t.ROW_FORMAT\nFROM information_schema",
        ]);

        return ['sql' => $sql];
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::fetchTableOptionsByTable()
     */
    #[Intercept]
    protected function fetchTableOptionsByTableByTableOptionForTableOptions($tableOptions, $metadata): array
    {
        foreach ($tableOptions as $name => $tableOption) {
            if (isset($metadata[$name])) {
                $tableOptions[$name]['row_format'] = $metadata[$name]['ROW_FORMAT'];
            }
        }

        return ['tableOptions' => $tableOptions];
    }
}
