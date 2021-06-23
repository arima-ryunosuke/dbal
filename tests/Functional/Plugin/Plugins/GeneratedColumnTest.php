<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class GeneratedColumnTest extends AbstractPluginTestCase
{
    public function testGeneratedColumn(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        self::assertEquals(
            'foo VARCHAR(255) AS (EXPR) STORED',
            $platform->getColumnDeclarationSQL('foo', [
                'type'       => Type::getType('string'),
                'generation' => [
                    'type'       => 'STORED',
                    'expression' => 'EXPR',
                ],
            ])
        );

        self::assertEquals(
            'foo INT AS (EXPR) VIRTUAL NOT NULL',
            $platform->getColumnDeclarationSQL('foo', [
                'type'       => Type::getType('integer'),
                'notnull'    => true,
                'generation' => [
                    'type'       => 'VIRTUAL',
                    'expression' => 'EXPR',
                ],
            ])
        );
    }

    public function testGeneratedColumnOnline(): void
    {
        $table = new Table('test_generated');
        $table->addColumn('id', 'integer');
        $table->addColumn('name', 'string');
        $table->addColumn('idname', 'text')->setPlatformOption('generation', [
            'type'       => 'STORED',
            'expression' => 'CONCAT(id, "-", name)',
        ]);
        $this->dropAndCreateTable($table);

        $schemaManager = $this->connection->createSchemaManager();
        $columns       = $schemaManager->listTableColumns('test_generated');
        self::assertNotFalse($columns);

        self::assertEquals([
            'type'       => 'STORED',
            'expression' => "concat(`id`,_utf8mb4'-',`name`)",
        ], $columns['idname']->getPlatformOptions()['generation']);
    }
}
