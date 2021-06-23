<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class IndexOptionTest extends AbstractPluginTestCase
{
    public function testDiffIndex(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $comparator    = $schemaManager->createComparator();

        $diffIndex = (fn(...$args) => $this->diffIndex(...$args))->bindTo($comparator, Comparator::class);

        self::assertFalse($diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertTrue($diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2', 'c3'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertTrue($diffIndex(
            new Index('hoge', ['c1', 'c2'], true, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertFalse($diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag2', 'flag1'], ['option' => 1]),
        ));
        self::assertTrue($diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertTrue($diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag'], ['option' => 2]),
        ));
    }

    public function testWithParser(): void
    {
        $misc  = ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_bin'];
        $table = new Table("foo", [
            new Column('id', Type::getType('integer')),
            new Column('first_name', Type::getType('string'), ['length' => 255, 'platformOptions' => $misc]),
            new Column('last_name', Type::getType('string'), ['length' => 255, 'platformOptions' => $misc]),
        ], [
            $index1 = new Index('PRIMARY', ['id'], true, true),
            $index2 = new Index('ftx_name', ['first_name', 'last_name'], false, false, ['FULLTEXT'], ['parser' => 'ngram']),
        ], [], [], $misc);

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();

        self::assertEquals('ALTER TABLE foo ADD PRIMARY KEY (id)', $platform->getCreateIndexSQL($index1, $table->getName()));
        self::assertEquals('CREATE FULLTEXT INDEX ftx_name ON foo (first_name, last_name) WITH PARSER ngram', $platform->getCreateIndexSQL($index2, $table->getName()));

        $this->dropAndCreateTable($table);
        $onlineTable = $schemaManager->introspectTable("foo");
        $index       = $onlineTable->getIndex('ftx_name');

        self::assertTrue($index->hasFlag('fulltext'));
        self::assertEquals('ngram', $index->getOption('parser'));

        $onlineTable->dropIndex('ftx_name');
        $onlineTable->addIndex(['first_name', 'last_name'], 'ftx_name2', ['FULLTEXT'], ['parser' => 'custom']);

        self::assertEquals([
            implode(', ', [
                'ALTER TABLE foo DROP INDEX ftx_name',
                'ADD FULLTEXT INDEX ftx_name2 (first_name, last_name) WITH PARSER custom',
            ]),
        ], $platform->getAlterTableSQL($schemaManager->createComparator()->compareTables($table, $onlineTable)));
    }

    public function testExpression(): void
    {
        $misc  = ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_bin'];
        $table = new Table("foo", [
            new Column('id', Type::getType('integer')),
            new Column('first_name', Type::getType('string'), ['length' => 255, 'platformOptions' => $misc]),
            new Column('last_name', Type::getType('string'), ['length' => 255, 'platformOptions' => $misc]),
        ], [
            $index1 = new Index('PRIMARY', ['id'], true, true),
            $index2 = new Index('idx_concat', [], false, false, [], ['expression' => 'concat(first_name, last_name)']),
        ], [], [], $misc);

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();

        self::assertEquals('ALTER TABLE foo ADD PRIMARY KEY (id)', $platform->getCreateIndexSQL($index1, $table->getName()));
        self::assertEquals('CREATE INDEX idx_concat ON foo ((concat(first_name, last_name)))', $platform->getCreateIndexSQL($index2, $table->getName()));

        $this->dropAndCreateTable($table);
        $onlineTable = $schemaManager->introspectTable("foo");
        $index       = $onlineTable->getIndex('idx_concat');

        self::assertEquals('concat(`first_name`,`last_name`)', $index->getOption('expression'));

        $onlineTable->dropIndex('idx_concat');
        $onlineTable->addIndex([], 'idx_concat', [], ['expression' => 'concat(first_name, "2")']);

        self::assertEquals([
            'DROP INDEX idx_concat ON foo',
            'CREATE INDEX idx_concat ON foo ((concat(first_name, "2")))',
        ], $platform->getAlterTableSQL($schemaManager->createComparator()->compareTables($table, $onlineTable)));

        // treat doctrine4 bug(IndexColumnMetadataRow's $columnName requres string(not null))
        $schemaManager->dropIndex('idx_concat', 'foo');
    }
}
