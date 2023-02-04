<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Functional\Platform;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Tests\FunctionalTestCase;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Types;

class OtherSchemaTest extends FunctionalTestCase
{
    private string $nesting_db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nesting_db = sys_get_temp_dir() . '/test_other_schema.sqlite';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->nesting_db)) {
            unlink($this->nesting_db);
        }

        parent::tearDown();
    }

    public function testATableCanBeCreatedInAnotherSchema(): void
    {
        $databasePlatform = $this->connection->getDatabasePlatform();
        if (! ($databasePlatform instanceof SQLitePlatform)) {
            self::markTestSkipped('This test requires SQLite');
        }

        $this->connection->executeStatement("ATTACH DATABASE '$this->nesting_db' AS other");

        $table = Table::editor()
            ->setUnquotedName('test_other_schema', 'other')
            ->setColumns(
                Column::editor()
                    ->setUnquotedName('id')
                    ->setTypeName(Types::INTEGER)
                    ->create(),
            )
            ->create();

        $table->addIndex(['id']);

        $this->dropAndCreateTable($table);
        $this->connection->insert('other.test_other_schema', ['id' => 1]);

        self::assertEquals(1, $this->connection->fetchOne('SELECT COUNT(*) FROM other.test_other_schema'));
        $dsnParser   = new DsnParser();
        $connection  = DriverManager::getConnection(
            $dsnParser->parse("sqlite3:///$this->nesting_db"),
        );
        $onlineTable = $connection->createSchemaManager()->introspectTableByUnquotedName('test_other_schema');
        self::assertCount(1, $onlineTable->getIndexes());

        $connection->close();
        $this->connection->close();
    }
}
