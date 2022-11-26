<?php

namespace Doctrine\DBAL\Plugin\TableOption\Schema;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::fetchTableOptionsByTable()
     */
    protected function interceptFetchTableOptionsByTableByTableOptionForSql(string $sql): array
    {
        $sql = strtr($sql, [
            'FROM information_schema' => ",t.ROW_FORMAT\nFROM information_schema",
        ]);

        return ['sql' => $sql];
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::fetchTableOptionsByTable()
     */
    protected function interceptFetchTableOptionsByTableByTableOptionForTableOptions($tableOptions, $metadata): array
    {
        foreach ($tableOptions as $name => $tableOption) {
            if (isset($metadata[$name])) {
                $tableOptions[$name]['row_format'] = $metadata[$name]['ROW_FORMAT'];
            }
        }

        return ['tableOptions' => $tableOptions];
    }
}
