<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;

class TableOptionTest extends AbstractPluginTestCase
{
    public function testTableOptions(): void
    {
        $table1 = new Table('foo');
        $table1->addOption('hoge', 'foo');

        $table2 = new Table('foo');
        $table2->addOption('hoge', 'bar');

        $schemaManager = $this->connection->createSchemaManager();
        $tableDiff     = $schemaManager->createComparator()->compareTables($table1, $table2);

        self::assertInstanceOf(TableDiff::class, $tableDiff);
        self::assertEquals(1, count($tableDiff->changedOptions));
    }

    public function testChangeTableOption(): void
    {
        $tableA = new Table('foo');
        $tableA->addColumn('id', 'integer');
        $tableA->addOption('charset', 'utf8mb3');
        $tableA->addOption('comment', 'A-table');
        $tableA->addOption('engine', 'MyISAM');
        $tableA->addOption('row_format', 'Compact');

        $tableB = new Table('foo');
        $tableB->addColumn('id', 'integer');
        $tableB->addOption('charset', 'utf8mb4');
        $tableB->addOption('comment', 'B-table');
        $tableB->addOption('engine', 'InnoDB');
        $tableB->addOption('row_format', 'Dynamic');

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $tableDiff     = $schemaManager->createComparator()->compareTables($tableA, $tableB);

        self::assertInstanceOf(TableDiff::class, $tableDiff);
        self::assertArrayHasKey('comment', $tableDiff->changedOptions);
        self::assertFalse($tableDiff->isEmpty());

        if ($platform instanceof MySQLPlatform) {
            self::assertEquals([
                "ALTER TABLE foo DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB COMMENT = 'B-table' ROW_FORMAT = Dynamic",
            ], $platform->getAlterTableSQL($tableDiff));
        }
    }
}
