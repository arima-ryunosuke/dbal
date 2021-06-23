<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\Table;

class PositionalColumnTest extends AbstractPluginTestCase
{
    public function testColumnPosition(): void
    {
        $oldTable = new Table("foo");
        $oldTable->addColumn('c2', 'integer')->setPlatformOption('beforeColumn', null);
        $oldTable->addColumn('c8', 'integer')->setPlatformOption('beforeColumn', null);
        $oldTable->addColumn('c9', 'integer')->setPlatformOption('beforeColumn', null);

        $newtable = new Table("foo");
        $newtable->addColumn('c1', 'integer')->setPlatformOption('beforeColumn', null);
        $newtable->addColumn('c2', 'integer')->setPlatformOption('beforeColumn', 'c1');
        $newtable->addColumn('c3', 'integer')->setPlatformOption('beforeColumn', 'c2');
        $newtable->addColumn('c4', 'integer')->setPlatformOption('beforeColumn', 'c3');
        $newtable->addColumn('c8', 'integer')->setPlatformOption('beforeColumn', 'c3');
        $newtable->addColumn('c9', 'string', ['length' => 255])->setPlatformOption('beforeColumn', 'c4');

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $diff          = $schemaManager->createComparator()->compareTables($oldTable, $newtable);
        self::assertNotFalse($diff);

        self::assertEquals([
            implode(', ', [
                'ALTER TABLE foo ADD c1 INT NOT NULL FIRST',
                'ADD c3 INT NOT NULL AFTER c2',
                'ADD c4 INT NOT NULL AFTER c3',
                'CHANGE c9 c9 VARCHAR(255) NOT NULL AFTER c4',
            ]),
        ], $platform->getAlterTableSQL($diff));
    }

    public function testColumnPositionOnline(): void
    {
        $table = new Table('test_position');
        $table->addColumn('id', 'integer');
        $table->addColumn('text', 'text');
        $table->addColumn('foo', 'text');
        $table->addColumn('bar', 'text');
        $this->dropAndCreateTable($table);

        $schemaManager = $this->connection->createSchemaManager();
        $columns       = $schemaManager->listTableColumns('test_position');
        self::assertNotFalse($columns);

        self::assertArrayNotHasKey('beforeColumn', $columns['id']->getPlatformOptions());
        self::assertEquals('id', $columns['text']->getPlatformOption('beforeColumn'));
        self::assertEquals('text', $columns['foo']->getPlatformOption('beforeColumn'));
        self::assertEquals('foo', $columns['bar']->getPlatformOption('beforeColumn'));
    }
}
