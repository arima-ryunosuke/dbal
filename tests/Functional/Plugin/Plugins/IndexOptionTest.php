<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class IndexOptionTest extends AbstractPluginTestCase
{
    public function testDiffIndex(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $comparator    = $schemaManager->createComparator();

        self::assertFalse($comparator->diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertTrue($comparator->diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2', 'c3'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertTrue($comparator->diffIndex(
            new Index('hoge', ['c1', 'c2'], true, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertFalse($comparator->diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag2', 'flag1'], ['option' => 1]),
        ));
        self::assertTrue($comparator->diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag1'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag1', 'flag2'], ['option' => 1]),
        ));
        self::assertTrue($comparator->diffIndex(
            new Index('hoge', ['c1', 'c2'], false, false, ['flag'], ['option' => 1]),
            new Index('fuga', ['c1', 'c2'], false, false, ['flag'], ['option' => 2]),
        ));
    }

    public function testWithParser(): void
    {
        $misc  = ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_bin'];
        $table = new Table("foo", [
            new Column('id', Type::getType('integer')),
            new Column('first_name', Type::getType('string'), ['platformOptions' => $misc]),
            new Column('last_name', Type::getType('string'), ['platformOptions' => $misc]),
        ], [
            new Index('PRIMARY', ['id'], true, true),
            new Index('ftx_name', ['first_name', 'last_name'], false, false, ['FULLTEXT'], ['parser' => 'ngram']),
        ], [], [], $misc);

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();

        self::assertEquals([
            'CREATE TABLE foo (' . implode(', ', [
                'id INT NOT NULL',
                'first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`',
                'last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`',
                'FULLTEXT INDEX ftx_name (first_name, last_name) WITH PARSER ngram',
                'PRIMARY KEY(id)',
            ]) . ') DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_bin` ENGINE = InnoDB',
        ], $platform->getCreateTableSQL($table));

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
            new Column('first_name', Type::getType('string'), ['platformOptions' => $misc]),
            new Column('last_name', Type::getType('string'), ['platformOptions' => $misc]),
        ], [
            new Index('PRIMARY', ['id'], true, true),
            new Index('idx_concat', [], false, false, [], ['expression' => 'concat(first_name, last_name)']),
        ], [], [], $misc);

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();

        self::assertEquals([
            'CREATE TABLE foo (' . implode(', ', [
                'id INT NOT NULL',
                'first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`',
                'last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`',
                'INDEX idx_concat ((concat(first_name, last_name)))',
                'PRIMARY KEY(id)',
            ]) . ') DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_bin` ENGINE = InnoDB',
        ], $platform->getCreateTableSQL($table));

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
    }
}
