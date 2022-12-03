<?php

namespace Doctrine\DBAL\Plugin\IndexOption\Schema;

use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Schema\Index;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::selectIndexColumns()
     */
    protected function interceptSelectIndexColumnsByIndexOption(string $sql)
    {
        $expression = $this->_conn->getDatabasePlatform() instanceof MySQL80Platform ? 'EXPRESSION' : 'NULL';

        $sql = strtr($sql, [
            'FROM information_schema' => ",$expression AS expression\nFROM information_schema",
        ]);

        return ['sql' => $sql];
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableIndexesList()
     */
    protected function intercept_GetPortableTableIndexesListByIndexOptionForResult(array $result)
    {
        foreach ($result as &$index) {
            $index['columns'] = array_filter($index['columns'], fn($column) => $column !== null);

            if (isset($index['tableIndex']['expression'])) {
                $index['options']['expression'] = $index['tableIndex']['expression'];
            }
        }

        return ['result' => $result];
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableTableIndexesList()
     */
    protected function intercept_GetPortableTableIndexesListByIndexOptionForIndex(array $indexes, $tableName)
    {
        /** @var Index $index */
        foreach ($indexes as $index) {
            $creation = $this->_conn->fetchAssociative("SHOW CREATE TABLE `$tableName`")['Create Table'];
            if (preg_match("#`" . $index->getName() . "`.*?50100 WITH PARSER `(.+)`#i", $creation, $matches)) {
                $index->addOption('parser', $matches[1]);
            }
        }
    }
}
