<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\Schema;

class RoutineTest extends AbstractPluginTestCase
{
    public function testPlatform(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        self::assertTrue($platform->supportsRoutines());
    }

    public function testDiffRoutine(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $comparator    = $schemaManager->createComparator();

        self::assertEquals([], $comparator->diffRoutine(
            new Routine('hoge', 'select 1', ['option' => 1]),
            new Routine('fuga', 'select 1', ['option' => 1]),
        ));
        self::assertEquals([
            [
                'statement' => 'select 1',
            ],
            [
                'statement' => 'select 2',
            ],
        ], $comparator->diffRoutine(
            new Routine('hoge', 'select 1', ['option' => 1]),
            new Routine('fuga', 'select 2', ['option' => 1]),
        ));
        self::assertEquals([
            [
                'options' => ['option' => 1],
            ],
            [
                'options' => ['option' => 2],
            ],
        ], $comparator->diffRoutine(
            new Routine('hoge', 'select 1', ['option' => 1]),
            new Routine('fuga', 'select 1', ['option' => 2]),
        ));
    }

    public function testRoutine(): void
    {
        $poptions       = [
            'type'          => Routine::TYPE_PROCEDURE,
            'parameters'    => [
                'arg1' => [
                    'mode'            => 'IN',
                    'typeDeclaration' => 'tinyint',
                ],
                'arg2' => [
                    'mode'            => 'OUT',
                    'typeDeclaration' => 'datetime',
                ],
            ],
            'language'      => 'sql',
            'nullcall'      => true,
            'deterministic' => true,
            'dataAccess'    => 'dummy',
            'securityType'  => 'DEFINER',
            'definer'       => '',
            'comment'       => 'this is comment',
        ];
        $foptions       = [
            'type'                  => Routine::TYPE_FUNCTION,
            'returnTypeDeclaration' => 'varchar(1)',
            'parameters'            => [
                'arg1' => [
                    'mode'            => 'IN',
                    'typeDeclaration' => 'tinyint',
                ],
                'arg2' => [
                    'mode'            => 'OUT',
                    'typeDeclaration' => 'datetime',
                ],
            ],
            'language'              => 'sql',
            'nullcall'              => true,
            'deterministic'         => false,
            'dataAccess'            => 'dummy',
            'securityType'          => 'DEFINER',
            'definer'               => '',
            'comment'               => 'this is comment',
        ];
        $changedOptions = [
            'nullcall'     => false,
            'dataAccess'   => 'dummy2',
            'securityType' => 'INVOKER',
            'comment'      => 'this is comment2',
        ];

        $schema1 = new Schema();
        $schema1->addRoutine(new Routine('nochanged', 'select 1', $poptions));
        $schema1->addRoutine(new Routine('altered1', 'select 1', $poptions));
        $schema1->addRoutine(new Routine('altered2', 'select 2', $foptions));
        $schema1->addRoutine(new Routine('altered3', 'select 3', $poptions));
        $schema1->addRoutine(new Routine('altered4', 'select 4', $foptions));
        $schema1->addRoutine(new Routine('altered5', 'select 5', $poptions));
        $schema1->addRoutine(new Routine('altered6', 'select 6', $foptions));
        $schema1->addRoutine($droppedRoutine1 = new Routine('dropped1', 'select 1', $poptions));
        $schema1->addRoutine($droppedRoutine2 = new Routine('dropped2', 'select 1', $foptions));

        $schema2 = new Schema();
        $schema2->addRoutine(new Routine('nochanged', 'select 1', $poptions));
        $schema2->addRoutine($alteredRoutine1 = new Routine('altered1', 'select 8', $poptions));
        $schema2->addRoutine($alteredRoutine2 = new Routine('altered2', 'select 9', $foptions));
        $schema2->addRoutine($alteredRoutine3 = new Routine('altered3', 'select 3', $changedOptions + $poptions));
        $schema2->addRoutine($alteredRoutine4 = new Routine('altered4', 'select 4', $changedOptions + $foptions));
        $schema2->addRoutine($alteredRoutine5 = new Routine('altered5', 'select 5', ['parameters' => []] + $poptions));
        $schema2->addRoutine($alteredRoutine6 = new Routine('altered6', 'select 6', ['parameters' => []] + $foptions));
        $schema2->addRoutine($createdRoutine1 = new Routine('created1', 'select 1', ['definer' => 'user@localhost'] + $poptions));
        $schema2->addRoutine($createdRoutine2 = new Routine('created2', 'select 1', ['definer' => 'user@localhost'] + $foptions));

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $diffSchema    = $schemaManager->createComparator()->compareSchemas($schema1, $schema2);

        self::assertFalse($diffSchema->isEmpty());
        self::assertCount(1, $diffSchema->createdProcedures);
        self::assertCount(1, $diffSchema->createdFunctions);
        self::assertCount(3, $diffSchema->alteredProcedures);
        self::assertCount(3, $diffSchema->alteredFunctions);
        self::assertCount(1, $diffSchema->droppedProcedures);
        self::assertCount(1, $diffSchema->droppedFunctions);
        self::assertSame($createdRoutine1, $diffSchema->createdProcedures[0]);
        self::assertSame($createdRoutine2, $diffSchema->createdFunctions[0]);
        self::assertSame($alteredRoutine1, $diffSchema->alteredProcedures[0]);
        self::assertSame($alteredRoutine2, $diffSchema->alteredFunctions[0]);
        self::assertSame($alteredRoutine3, $diffSchema->alteredProcedures[1]);
        self::assertSame($alteredRoutine4, $diffSchema->alteredFunctions[1]);
        self::assertSame($alteredRoutine5, $diffSchema->alteredProcedures[2]);
        self::assertSame($alteredRoutine6, $diffSchema->alteredFunctions[2]);
        self::assertSame($droppedRoutine1, $diffSchema->droppedProcedures[0]);
        self::assertSame($droppedRoutine2, $diffSchema->droppedFunctions[0]);

        $expected = (function ($platform) {
            switch (true) {
                case $platform instanceof MySQLPlatform:
                    return [
                        'created1' => [
                            <<<'SQLs'
                            CREATE DEFINER=`user`@`localhost` PROCEDURE created1(IN arg1 tinyint, OUT arg2 datetime)
                            DETERMINISTIC
                            dummy
                            SQL SECURITY DEFINER
                            COMMENT 'this is comment'
                            select 1
                            SQLs,
                        ],
                        'created2' => [
                            <<<'SQLs'
                            CREATE DEFINER=`user`@`localhost` FUNCTION created2(arg1 tinyint, arg2 datetime)
                            RETURNS varchar(1)
                            NOT DETERMINISTIC
                            dummy
                            SQL SECURITY DEFINER
                            COMMENT 'this is comment'
                            select 1
                            SQLs,
                        ],
                        'dropped1' => [
                            'DROP PROCEDURE dropped1',
                        ],
                        'dropped2' => [
                            'DROP FUNCTION dropped2',
                        ],
                        'altered1' => [
                            'DROP PROCEDURE altered1',
                            <<<'SQLs'
                            CREATE PROCEDURE altered1(IN arg1 tinyint, OUT arg2 datetime)
                            DETERMINISTIC
                            dummy
                            SQL SECURITY DEFINER
                            COMMENT 'this is comment'
                            select 8
                            SQLs,
                        ],
                        'altered2' => [
                            'DROP FUNCTION altered2',
                            <<<'SQLs'
                            CREATE FUNCTION altered2(arg1 tinyint, arg2 datetime)
                            RETURNS varchar(1)
                            NOT DETERMINISTIC
                            dummy
                            SQL SECURITY DEFINER
                            COMMENT 'this is comment'
                            select 9
                            SQLs,
                        ],
                        'altered3' => [
                            <<<'SQLs'
                            ALTER PROCEDURE altered3
                            dummy2
                            SQL SECURITY INVOKER
                            COMMENT 'this is comment2'
                            SQLs,
                        ],
                        'altered4' => [
                            <<<'SQLs'
                            ALTER FUNCTION altered4
                            dummy2
                            SQL SECURITY INVOKER
                            COMMENT 'this is comment2'
                            SQLs,
                        ],
                        'altered5' => [
                            'DROP PROCEDURE altered5',
                            <<<'SQLs'
                            CREATE PROCEDURE altered5()
                            DETERMINISTIC
                            dummy
                            SQL SECURITY DEFINER
                            COMMENT 'this is comment'
                            select 5
                            SQLs,
                        ],
                        'altered6' => [
                            'DROP FUNCTION altered6',
                            <<<'SQLs'
                            CREATE FUNCTION altered6()
                            RETURNS varchar(1)
                            NOT DETERMINISTIC
                            dummy
                            SQL SECURITY DEFINER
                            COMMENT 'this is comment'
                            select 6
                            SQLs,
                        ],
                    ];
                case $platform instanceof PostgreSQLPlatform:
                    return [
                        'created1' => [
                            <<<'SQL'
                            CREATE PROCEDURE created1(IN arg1 tinyint, OUT arg2 datetime)
                            AS $$select 1$$
                            LANGUAGE sql
                            SQL,
                        ],
                        'created2' => [
                            <<<'SQL'
                            CREATE FUNCTION created2(IN arg1 tinyint, OUT arg2 datetime)
                            RETURNS varchar(1)
                            AS $$select 1$$
                            LANGUAGE sql
                            VOLATILE
                            RETURNS NULL ON NULL INPUT
                            SQL,
                        ],
                        'dropped1' => [
                            'DROP PROCEDURE dropped1(IN arg1 tinyint, OUT arg2 datetime)',
                        ],
                        'dropped2' => [
                            'DROP FUNCTION dropped2(IN arg1 tinyint, OUT arg2 datetime)',
                        ],
                        'altered1' => [
                            'DROP PROCEDURE altered1(IN arg1 tinyint, OUT arg2 datetime)',
                            <<<'SQL'
                            CREATE PROCEDURE altered1(IN arg1 tinyint, OUT arg2 datetime)
                            AS $$select 8$$
                            LANGUAGE sql
                            SQL,
                        ],
                        'altered2' => [
                            'DROP FUNCTION altered2(IN arg1 tinyint, OUT arg2 datetime)',
                            <<<'SQL'
                            CREATE FUNCTION altered2(IN arg1 tinyint, OUT arg2 datetime)
                            RETURNS varchar(1)
                            AS $$select 9$$
                            LANGUAGE sql
                            VOLATILE
                            RETURNS NULL ON NULL INPUT
                            SQL,
                        ],
                        'altered3' => [
                            'ALTER PROCEDURE altered3',
                        ],
                        'altered4' => [
                            <<<'SQL'
                            ALTER FUNCTION altered4
                            VOLATILE
                            CALLED ON NULL INPUT
                            SQL,
                        ],
                        'altered5' => [
                            'DROP PROCEDURE altered5(IN arg1 tinyint, OUT arg2 datetime)',
                            <<<'SQL'
                            CREATE PROCEDURE altered5()
                            AS $$select 5$$
                            LANGUAGE sql
                            SQL,
                        ],
                        'altered6' => [
                            'DROP FUNCTION altered6(IN arg1 tinyint, OUT arg2 datetime)',
                            <<<'SQL'
                            CREATE FUNCTION altered6()
                            RETURNS varchar(1)
                            AS $$select 6$$
                            LANGUAGE sql
                            VOLATILE
                            RETURNS NULL ON NULL INPUT
                            SQL,
                        ],
                    ];
            }
        })($platform);

        $actual = $platform->getAlterSchemaSQL($diffSchema);
        foreach ($expected as $key => $sqls) {
            foreach ($sqls as $sql) {
                $message = "$key failed\n" . implode("\n", array_filter($actual, fn($v) => str_contains($v, $key)));
                self::assertContains($sql, $actual, $message);
            }
        }
    }

    public function testRoutineOnline(): void
    {
        $onlineRoutine  = new Routine('test_onlineroutine', 'BEGIN RETURN NOW();END', [
            'type'                  => Routine::TYPE_FUNCTION,
            'returnTypeDeclaration' => 'varchar(1)',
            'parameters'            => [
                'arg1' => [
                    'mode'            => 'IN',
                    'typeDeclaration' => 'int',
                ],
                'arg2' => [
                    'mode'            => 'IN',
                    'typeDeclaration' => 'date',
                ],
            ],
            'language'              => 'plpgsql',
            'nullcall'              => true,
            'deterministic'         => false,
            'dataAccess'            => 'READS SQL DATA',
            'securityType'          => 'DEFINER',
            'definer'               => 'user@localhost',
            'comment'               => 'this is comment',
        ]);
        $offlineRoutine = new Routine('test_offlineroutine', 'select 123', [
            'type'                  => Routine::TYPE_PROCEDURE,
            'returnTypeDeclaration' => '',
            'parameters'            => [],
            'language'              => 'sql',
            'nullcall'              => true,
            'deterministic'         => false,
            'dataAccess'            => 'READS SQL DATA',
            'securityType'          => 'INVOKER',
            'definer'               => 'user@localhost',
            'comment'               => 'this is comment',
        ]);

        $schemaManager = $this->connection->createSchemaManager();
        $routineName   = $this->dropAndCreateSchemaObject($onlineRoutine);
        $schema        = $schemaManager->introspectSchema();

        self::assertSame($schema, $schema->addRoutine($offlineRoutine));
        self::assertCount(2, $schema->getRoutines());
        self::assertInstanceOf(Routine::class, $schema->getRoutine($routineName));
        self::assertTrue($schema->hasRoutine($routineName));
        self::assertSame($schema, $schema->dropRoutine($routineName));
        self::assertCount(1, $schema->getRoutines());

        // no error
        $schemaManager->createRoutine($offlineRoutine);
        $schemaManager->dropRoutine($offlineRoutine->getName(), $offlineRoutine->getType());

        self::assertException('already exists', fn() => $schema->addRoutine($offlineRoutine));
        self::assertException('There is no', fn() => $schema->dropRoutine('undefined'));
    }
}
