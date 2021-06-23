<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class ImplicitIndexTest extends AbstractPluginTestCase
{
    protected bool $autoskip = false;

    public function testImplicitIndex_false(): void
    {
        $config = new SchemaConfig();
        $config->setExplicitForeignKeyIndexes(false);

        $table = new Table('foo', [
            new Column('id', Type::getType(Types::INTEGER)),
            new Column('id2', Type::getType(Types::INTEGER)),
            new Column('name', Type::getType(Types::STRING)),
        ]);
        $table->setSchemaConfig($config);

        $table->addUniqueConstraint(['name']);
        $table->addForeignKeyConstraint('foo', ['id'], ['id2']);

        $this->assertCount(1, $table->getIndexes());
    }

    public function testImplicitIndex_true(): void
    {
        $config = new SchemaConfig();
        $config->setExplicitForeignKeyIndexes(true);

        $table = new Table('foo', [
            new Column('id', Type::getType(Types::INTEGER)),
            new Column('id2', Type::getType(Types::INTEGER)),
            new Column('name', Type::getType(Types::STRING)),
        ]);
        $table->setSchemaConfig($config);

        $table->addUniqueConstraint(['name']);
        $table->addForeignKeyConstraint('foo', ['id'], ['id2']);

        $this->assertCount(0, $table->getIndexes());
    }
}
