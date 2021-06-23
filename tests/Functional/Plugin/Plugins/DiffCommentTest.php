<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;

class DiffCommentTest extends AbstractPluginTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection->getDatabasePlatform()->enableDiffComment(true);
    }

    protected function tearDown(): void
    {
        $this->connection->getDatabasePlatform()->enableDiffComment(false);

        parent::tearDown();
    }

    public function testDiffComment(): void
    {
        $table1 = new Table('foo');
        $table1->addOption('hoge', 'foo');
        $table1->addColumn('col1', 'integer', ['notnull' => true]);

        $table2 = new Table('foo');
        $table2->addOption('hoge', 'bar');
        $table2->addColumn('col1', 'string', ['notnull' => false, 'length' => 255]);

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $tableDiff     = $schemaManager->createComparator()->compareTables($table1, $table2);

        self::assertInstanceOf(TableDiff::class, $tableDiff);
        self::assertEquals([
            ['hoge' => 'foo'],
            ['hoge' => 'bar'],
        ], $tableDiff->getDiff());
        self::assertEquals([
            ['type' => Type::getType('integer'), 'notnull' => true],
            ['type' => Type::getType('string'), 'notnull' => false],
        ], $tableDiff->getChangedColumns()['col1']->getDiff());

        $table  = new Table('bar');
        $column = new Column('foo', Type::getType('integer'));
        self::assertEquals([[], []], (new TableDiff($table))->getDiff());
        self::assertEquals([[], []], (new ColumnDiff($column, $column))->getDiff());

        if ($platform instanceof MySQLPlatform) {
            $sqls = $platform->getAlterTableSQL($tableDiff);
            self::assertStringContainsString('hoge: "foo" => "bar"', $sqls[0]);
            self::assertStringContainsString('integer => string', $sqls[0]);
            self::assertStringContainsString('notnull: true => false', $sqls[0]);
        }
    }
}
