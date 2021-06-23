<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Trigger;

class TriggerTest extends AbstractPluginTestCase
{
    public function testPlatform(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        self::assertTrue($platform->supportsTriggers());
        self::assertEquals(match (true) {
            $platform instanceof SQLitePlatform     => true,
            $platform instanceof MySQLPlatform      => true,
            $platform instanceof PostgreSQLPlatform => false,
        }, $platform->supportsInlineTriggers());

        self::assertEquals(match (true) {
            $platform instanceof SQLitePlatform     => 'BEGIN stmt; END',
            $platform instanceof MySQLPlatform      => 'stmt',
            $platform instanceof PostgreSQLPlatform => 'EXECUTE FUNCTION stmt',
        }, $platform->buildTriggerStatement('stmt'));

        if (!$platform instanceof PostgreSQLPlatform) {
            self::assertEquals('BEGIN stmt1(); stmt2(); END', $platform->buildTriggerStatement(['stmt1()', 'stmt2()']));
        }
    }

    public function testDiffTrigger(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $comparator    = $schemaManager->createComparator();

        self::assertEquals([], $comparator->diffTrigger(
            new Trigger('hoge', 'select 1', 'tableA', ['option' => 1]),
            new Trigger('fuga', 'select 1', 'tableA', ['option' => 1]),
        ));
        self::assertEquals([
            [
                'statement' => 'select 1',
            ],
            [
                'statement' => 'select 2',
            ],
        ], $comparator->diffTrigger(
            new Trigger('hoge', 'select 1', 'tableA', ['option' => 1]),
            new Trigger('fuga', 'select 2', 'tableA', ['option' => 1]),
        ));
        self::assertEquals([
            [
                'table' => 'tableA',
            ],
            [
                'table' => 'tableB',
            ],
        ], $comparator->diffTrigger(
            new Trigger('hoge', 'select 1', 'tableA', ['option' => 1]),
            new Trigger('fuga', 'select 1', 'tableB', ['option' => 1]),
        ));
        self::assertEquals([
            [
                'options' => ['option' => 1],
            ],
            [
                'options' => ['option' => 2],
            ],
        ], $comparator->diffTrigger(
            new Trigger('hoge', 'select 1', 'tableA', ['option' => 1]),
            new Trigger('fuga', 'select 1', 'tableA', ['option' => 2]),
        ));
    }

    public function testTrigger(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        $schema1 = new Schema();
        $schema1->createTable('foo');
        $schema1->addTrigger(new Trigger('nochanged', $platform->buildTriggerStatement('statement1()'), 'foo', [
            'timing'  => 'AFTER',
            'event'   => 'UPDATE',
            'definer' => '',
        ]));
        $schema1->addTrigger(new Trigger('altered1', $platform->buildTriggerStatement('statement1()'), 'foo', [
            'timing'  => 'BEFORE',
            'event'   => 'UPDATE',
            'definer' => '',
        ]));
        $schema1->addTrigger(new Trigger('altered2', $platform->buildTriggerStatement('statement2()'), 'foo', [
            'timing'  => 'BEFORE',
            'event'   => 'UPDATE',
            'definer' => '',
        ]));
        $schema1->addTrigger($droppedTrigger = new Trigger('dropped', $platform->buildTriggerStatement('statement1()'), 'foo', [
            'timing'  => 'BEFORE',
            'event'   => 'INSERT',
            'definer' => '',
        ]));

        $schema2 = new Schema();
        $schema2->createTable('foo');
        $schema2->addTrigger(new Trigger('nochanged', $platform->buildTriggerStatement('statement1()'), 'foo', [
            'timing'  => 'AFTER',
            'event'   => 'UPDATE',
            'definer' => '',
        ]));
        $schema2->addTrigger($alteredTrigger1 = new Trigger('altered1', $platform->buildTriggerStatement('statement9()'), 'foo', [
            'timing'  => 'BEFORE',
            'event'   => 'UPDATE',
            'definer' => '',
        ]));
        $schema2->addTrigger($alteredTrigger2 = new Trigger('altered2', $platform->buildTriggerStatement('statement2()'), 'foo', [
            'timing'  => 'AFTER',
            'event'   => 'UPDATE',
            'definer' => '',
        ]));
        $schema2->addTrigger($createdTrigger = new Trigger('created', $platform->buildTriggerStatement('statement1()'), 'foo', [
            'timing'  => 'BEFORE',
            'event'   => 'INSERT',
            'definer' => 'user@localhost',
        ]));

        $schemaManager = $this->connection->createSchemaManager();
        $diffSchema    = $schemaManager->createComparator()->compareSchemas($schema1, $schema2);

        self::assertFalse($diffSchema->isEmpty());
        self::assertCount(1, $diffSchema->createdTriggers);
        self::assertCount(2, $diffSchema->alteredTriggers);
        self::assertCount(1, $diffSchema->droppedTriggers);
        self::assertSame($createdTrigger, $diffSchema->createdTriggers[0]);
        self::assertSame($alteredTrigger1, $diffSchema->alteredTriggers[0]);
        self::assertSame($alteredTrigger2, $diffSchema->alteredTriggers[1]);
        self::assertSame($droppedTrigger, $diffSchema->droppedTriggers[0]);

        $expected = (function ($platform) {
            switch (true) {
                case $platform instanceof SQLitePlatform:
                    return [
                        'CREATE TRIGGER created BEFORE INSERT ON foo FOR EACH ROW BEGIN statement1(); END',
                        'DROP TRIGGER IF EXISTS dropped',
                        'DROP TRIGGER IF EXISTS altered1',
                        'CREATE TRIGGER altered1 BEFORE UPDATE ON foo FOR EACH ROW BEGIN statement9(); END',
                        'DROP TRIGGER IF EXISTS altered2',
                        'CREATE TRIGGER altered2 AFTER UPDATE ON foo FOR EACH ROW BEGIN statement2(); END',
                    ];
                case $platform instanceof MySQLPlatform:
                    return [
                        'CREATE DEFINER=`user`@`localhost` TRIGGER created BEFORE INSERT ON foo FOR EACH ROW statement1()',
                        'DROP TRIGGER IF EXISTS dropped',
                        'DROP TRIGGER IF EXISTS altered1',
                        'CREATE TRIGGER altered1 BEFORE UPDATE ON foo FOR EACH ROW statement9()',
                        'DROP TRIGGER IF EXISTS altered2',
                        'CREATE TRIGGER altered2 AFTER UPDATE ON foo FOR EACH ROW statement2()',
                    ];
                case $platform instanceof PostgreSQLPlatform:
                    return [
                        'CREATE TRIGGER created BEFORE INSERT ON foo FOR EACH ROW EXECUTE FUNCTION statement1()',
                        'DROP TRIGGER IF EXISTS dropped ON foo',
                        'DROP TRIGGER IF EXISTS altered1 ON foo',
                        'CREATE TRIGGER altered1 BEFORE UPDATE ON foo FOR EACH ROW EXECUTE FUNCTION statement9()',
                        'DROP TRIGGER IF EXISTS altered2 ON foo',
                        'CREATE TRIGGER altered2 AFTER UPDATE ON foo FOR EACH ROW EXECUTE FUNCTION statement2()',
                    ];
            }
        })($platform);

        self::assertEquals($expected, $platform->getAlterSchemaSQL($diffSchema));
    }

    public function testTriggerOnline(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        $statement = null;
        switch (true) {
            case $platform instanceof SQLitePlatform:
                $statement = 'BEGIN insert into foo values (1); END';
                break;
            case $platform instanceof MySQLPlatform:
                $statement = 'insert into foo values (1)';
                break;
            case $platform instanceof PostgreSQLPlatform:
                $function = new Routine('trigger_statement', 'BEGIN RETURN NEW; END', [
                    'type'                  => Routine::TYPE_FUNCTION,
                    'returnTypeDeclaration' => 'trigger',
                    'parameters'            => [],
                    'language'              => 'plpgsql',
                    'nullcall'              => true,
                    'deterministic'         => false,
                    'dataAccess'            => 'READS SQL DATA',
                    'comment'               => 'dummy',
                ]);
                $this->dropAndCreateSchemaObject($function);
                $statement = 'EXECUTE FUNCTION trigger_statement()';
                break;
        }

        $table = new Table('test_trigger');
        $table->addColumn('id', 'integer');
        $this->dropAndCreateTable($table);

        $onlineTrigger  = new Trigger('test_onlinetrigger', $statement, 'test_trigger', [
            'timing'  => 'AFTER',
            'event'   => 'UPDATE',
            'definer' => '',
        ]);
        $offlineTrigger = new Trigger('test_offlinetrigger', $statement, 'test_trigger', [
            'timing'  => 'AFTER',
            'event'   => 'UPDATE',
            'definer' => '',
        ]);

        $schemaManager = $this->connection->createSchemaManager();
        $triggerName   = $this->dropAndCreateSchemaObject($onlineTrigger);
        $schema        = $schemaManager->introspectSchema();

        self::assertSame($schema, $schema->addTrigger($offlineTrigger));
        self::assertCount(2, $schema->getTriggers());
        self::assertInstanceOf(Trigger::class, $schema->getTrigger($triggerName));
        self::assertTrue($schema->hasTrigger($triggerName));
        self::assertSame($schema, $schema->dropTrigger($triggerName));
        self::assertCount(1, $schema->getTriggers());

        // no error
        $schemaManager->createTrigger($offlineTrigger);
        $schemaManager->dropTrigger($offlineTrigger->getName());

        self::assertException('already exists', fn() => $schema->addTrigger($offlineTrigger));
        self::assertException('There is no', fn() => $schema->getTrigger('undefined'));
        self::assertException('There is no', fn() => $schema->dropTrigger('undefined'));
    }

    public function testGeneratesTriggerSql(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        $trigger = new Trigger('trg_dummy', $platform->buildTriggerStatement('statement()'), 'test', [
            'timing'      => 'AFTER',
            'event'       => 'INSERT',
            'definer'     => '',
            'orientation' => 'STATEMENT',
        ]);

        $expected = (function ($platform) {
            switch (true) {
                case $platform instanceof SQLitePlatform:
                    return [
                        'create' => 'CREATE TRIGGER trg_dummy AFTER INSERT ON test FOR EACH ROW BEGIN statement(); END',
                        'drop'   => 'DROP TRIGGER IF EXISTS trg_dummy',
                    ];
                case $platform instanceof MySQLPlatform:
                    return [
                        'create' => 'CREATE TRIGGER trg_dummy AFTER INSERT ON test FOR EACH ROW statement()',
                        'drop'   => 'DROP TRIGGER IF EXISTS trg_dummy',
                    ];
                case $platform instanceof PostgreSQLPlatform:
                    return [
                        'create' => 'CREATE TRIGGER trg_dummy AFTER INSERT ON test FOR EACH STATEMENT EXECUTE FUNCTION statement()',
                        'drop'   => 'DROP TRIGGER IF EXISTS trg_dummy ON test',
                    ];
            }
        })($platform);

        self::assertEquals($expected['create'], $platform->getCreateTriggerSQL($trigger));
        self::assertEquals($expected['drop'], $platform->getDropTriggerSQL($trigger->getQuotedName($platform), $trigger->getTableName()));
    }
}
