<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\BitType;
use Doctrine\DBAL\Types\Type;

class CustomTypeTest extends AbstractPluginTestCase
{
    protected bool $autoskip = false;

    public function testAddCustomType(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        $platform->addCustomType('BIT', new BitType());
        $platform->addCustomType('BIT', new BitType());

        self::assertEquals('bit', Type::lookupName(Type::getType('bit')));

        self::assertException('Type "bit" already exists', fn() => $platform->addCustomType('BIT', new class ( ) extends BitType { }));
    }

    public function testBitTypeOnline(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        if (! $platform instanceof MySQLPlatform) {
            $this->markTestSkipped();
        }
        $platform->addCustomType('BIT', new BitType());

        $table = new Table('test_customtype');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('bitdata', 'bit', ['length' => 16, 'notNull' => false]);
        $table->setPrimaryKey(['id']);
        $this->dropAndCreateTable($table);

        $schemaManager = $this->connection->createSchemaManager();
        $columns       = $schemaManager->listTableColumns('test_customtype');
        self::assertNotFalse($columns);

        self::assertInstanceOf(BitType::class, $columns['bitdata']->getType());
        self::assertEquals(16, $columns['bitdata']->getLength());

        $binding = ['bitdata' => 'bit'];
        $this->connection->insert('test_customtype', ['bitdata' => 1], $binding);
        $this->connection->insert('test_customtype', ['bitdata' => "2"], $binding);
        $this->connection->insert('test_customtype', ['bitdata' => ""], $binding);
        $this->connection->insert('test_customtype', ['bitdata' => true], $binding);
        $this->connection->insert('test_customtype', ['bitdata' => false], $binding);
        $this->connection->insert('test_customtype', ['bitdata' => null], $binding);
        $this->connection->insert('test_customtype', ['bitdata' => "\n"], $binding);

        self::assertEquals([
            1 => '1',  // 1
            2 => '2',  // "2"
            3 => '0',  // ""
            4 => '1',  // true
            5 => '0',  // false
            6 => null, // null
            7 => '10', // "\n"
        ], $this->connection->fetchAllKeyValue('select id,bitdata from test_customtype'));

        $this->dropTableIfExists('test_customtype');
    }
}
