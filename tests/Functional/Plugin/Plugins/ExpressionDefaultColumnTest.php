<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class ExpressionDefaultColumnTest extends AbstractPluginTestCase
{
    public function testExpressiveDefaultColumn(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        self::assertEquals(
            "foo VARCHAR(255) DEFAULT 'hoge'",
            $platform->getColumnDeclarationSQL('foo', [
                'type'    => Type::getType('string'),
                'default' => "hoge",
            ])
        );

        self::assertEquals(
            'foo DATETIME DEFAULT (now())',
            $platform->getColumnDeclarationSQL('foo', [
                'type'              => Type::getType('datetime'),
                'default'           => 'now()',
                'expressionDefault' => true,
            ])
        );

        self::assertEquals(
            "foo LONGTEXT DEFAULT (_utf8mb4'')",
            $platform->getColumnDeclarationSQL('foo', [
                'type'              => Type::getType('text'),
                'default'           => "_utf8mb4''",
                'expressionDefault' => true,
            ])
        );
    }

    public function testExpressiveDefaultColumnOnline(): void
    {
        $table = new Table('test_edefault');
        $table->addColumn('id', 'integer');
        $table->addColumn('str', 'string')->setDefault("_utf8mb4''");
        $table->addColumn('dt', 'datetime')->setDefault('now()')->setPlatformOption('expressionDefault', true);
        $table->addColumn('txt', 'text')->setDefault("_utf8mb4''")->setPlatformOption('expressionDefault', true);
        $this->dropAndCreateTable($table);

        $schemaManager = $this->connection->createSchemaManager();
        $columns       = $schemaManager->listTableColumns('test_edefault');
        self::assertNotFalse($columns);

        self::assertEquals("_utf8mb4''", $columns['str']->getDefault());
        self::assertEquals(false, $columns['str']->hasPlatformOption('expressionDefault'));
        self::assertEquals('now()', $columns['dt']->getDefault());
        self::assertEquals(true, $columns['dt']->getPlatformOption('expressionDefault'));
        self::assertEquals("_utf8mb4''", $columns['txt']->getDefault());
        self::assertEquals(true, $columns['txt']->getPlatformOption('expressionDefault'));
    }
}
