<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\AbstractNamedObject;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Name\Parser\OptionallyQualifiedNameParser;
use Doctrine\DBAL\Schema\Name\Parsers;
use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Trigger;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\DBAL\Types\Type;

class UtilityTest extends AbstractPluginTestCase
{
    protected bool $autoskip = false;

    public function testMySQLPlatform(): void
    {
        $platform = new class extends MySqlPlatform {
            use \Doctrine\DBAL\Plugin\Utility\Platforms\MySQLPlatform {
                _quoteUserHostname as public quoteUserHostname;
            }
        };

        self::assertEquals('', $platform->quoteUserHostname(''));
        self::assertEquals('`user`', $platform->quoteUserHostname('user'));
        self::assertEquals('`user`@`hostname`', $platform->quoteUserHostname('user@hostname'));
    }

    public function testAbstractNamedObject(): void
    {
        $nameObject = new class('dummynamespace.hoge') extends AbstractNamedObject {
            use \Doctrine\DBAL\Plugin\Utility\Schema\AbstractNamedObject;

            protected function getNameParser(): OptionallyQualifiedNameParser
            {
                return Parsers::getOptionallyQualifiedNameParser();
            }
        };

        self::assertEquals('dummynamespace', $nameObject->getNamespace());
        self::assertEquals('hoge', $nameObject->getLocalName());

        $table = new Table('schema.tablename',
            [new Column('id', Type::getType('integer'))],
            [new Index('PRIMARY', ['id'], true, true)],
            [],
            [new ForeignKeyConstraint(['id'], 'fuga', ['fid'], 'fk01')],
        );
        $this->assertEquals('tablename', $table->getLocalName());
        $this->assertEquals('id', $table->getColumn('id')->getLocalName());
        $this->assertEquals('PRIMARY', $table->getIndex('PRIMARY')->getLocalName());
        $this->assertEquals('fk01', $table->getForeignKey('fk01')->getLocalName());
        $this->assertEquals('schema', $table->getNamespace());
        $this->assertEquals(null, $table->getColumn('id')->getNamespace());
        $this->assertEquals(null, $table->getIndex('PRIMARY')->getNamespace());
        $this->assertEquals(null, $table->getForeignKey('fk01')->getNamespace());

        $view = new View('schema.viewname', 'select 1');
        $this->assertEquals('viewname', $view->getLocalName());
        $this->assertEquals('schema', $view->getNamespace());

        $trigger = new Trigger('schema.triggername', 'select 1', 'tablename');
        $this->assertEquals('triggername', $trigger->getLocalName());
        $this->assertEquals('schema', $trigger->getNamespace());

        $routine = new Routine('schema.routinename', 'select 1');
        $this->assertEquals('routinename', $routine->getLocalName());
        $this->assertEquals('schema', $routine->getNamespace());
    }

    public function testSchema(): void
    {
        $schema = new class extends Schema {
            use \Doctrine\DBAL\Plugin\Utility\Schema\Schema {
                _createNamespaceBy as public createNamespaceBy;
            }

            public function resolveName(AbstractAsset $asset): AbstractAsset
            {
                if ($asset->getNamespaceName() === null) {
                    $defaultNamespaceName = $this->getName();

                    if ($defaultNamespaceName !== '') {
                        return new Identifier($defaultNamespaceName . '.' . $asset->getName());
                    }
                }

                return $asset;
            }
        };

        $schema->createNamespaceBy(new View('dummynamespace.dummyview', 'select 1'));
        self::assertEquals(['dummynamespace'], $schema->getNamespaces());
    }

    public function testTransactionIsolationLevel(): void
    {
        self::assertEquals('READ UNCOMMITTED', TransactionIsolationLevel::READ_UNCOMMITTED->toSQL());
        self::assertEquals('READ COMMITTED', TransactionIsolationLevel::READ_COMMITTED->toSQL());
        self::assertEquals('REPEATABLE READ', TransactionIsolationLevel::REPEATABLE_READ->toSQL());
        self::assertEquals('SERIALIZABLE', TransactionIsolationLevel::SERIALIZABLE->toSQL());
    }

    public function testReferentialAction(): void
    {
        self::assertEquals(true, ReferentialAction::CASCADE->hasAction());
        self::assertEquals(false, ReferentialAction::NO_ACTION->hasAction());
        self::assertEquals(true, ReferentialAction::SET_DEFAULT->hasAction());
        self::assertEquals(true, ReferentialAction::SET_NULL->hasAction());
        self::assertEquals(false, ReferentialAction::RESTRICT->hasAction());
    }
}
