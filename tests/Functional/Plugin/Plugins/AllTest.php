<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Tests\TestUtil;
use Doctrine\DBAL\Types\BitType;

class AllTest extends AbstractPluginTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection->getDatabasePlatform()->enableDiffComment(true);
        // call other switching method
    }

    protected function tearDown(): void
    {
        $this->connection->getDatabasePlatform()->enableDiffComment(false);
        // call other switching method

        parent::tearDown();
    }

    public function test_mysql(): void
    {
        if (! $this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->markTestSkipped();
        }

        $con1 = $this->connection;
        $con2 = TestUtil::getPrivilegedConnection();

        $con1->getDatabasePlatform()->enableAutoType(true);
        $con2->getDatabasePlatform()->enableAutoType(true);
        $con1->getDatabasePlatform()->addCustomType('BIT', new BitType());
        $con2->getDatabasePlatform()->addCustomType('BIT', new BitType());

        $dropSqls = [
            "DROP TABLE     IF EXISTS NewTable",
            "DROP TABLE     IF EXISTS DropTable",
            "DROP TABLE     IF EXISTS ExampleTable",
            "DROP VIEW      IF EXISTS ExampleView",
            "DROP PROCEDURE IF EXISTS procedure1",
            "DROP PROCEDURE IF EXISTS procedure2",
            "DROP FUNCTION  IF EXISTS function1",
            "DROP FUNCTION  IF EXISTS altered_function",
            "DROP EVENT     IF EXISTS event1",
            "DROP EVENT     IF EXISTS event2",
            "DROP EVENT     IF EXISTS event3",
        ];

        array_map(fn($sql) => $con1->executeStatement($sql), array_merge($dropSqls, [
            "CREATE TABLE DropTable (
              id INT(10) UNSIGNED NOT NULL,
              PRIMARY KEY (id)
            )",

            "CREATE TABLE ExampleTable (
              id INT(10) UNSIGNED NOT NULL,
              pos INT(10) UNSIGNED NOT NULL,
              seq INT(10) UNSIGNED NOT NULL,
              hashcode BINARY(32) NOT NULL,
              content TEXT DEFAULT (_utf8mb4'') NOT NULL COLLATE utf8mb4_bin,
              bindata BLOB NOT NULL,
              bitdata BIT(1) DEFAULT b'0' NOT NULL,
              enumdata ENUM('Y','N') NOT NULL,
              latlng GEOMETRY DEFAULT NULL,
              jsondata JSON,
              json_id INT(10) AS (jsondata->'$.id'),
              create_at DATETIME(3) DEFAULT (NOW()),
              PRIMARY KEY (id),
              KEY contentx (content(100)),
              KEY fnx ((id * 2)),
              FULLTEXT KEY ftx (content)
            ) Comment='comment_from' CHARSET=utf8mb4 COLLATE='utf8mb4_general_ci' ROW_FORMAT=COMPRESSED",

            "CREATE DEFINER=`user1`@`%` TRIGGER ExampleTable_trg BEFORE INSERT ON ExampleTable FOR EACH ROW
            BEGIN
              INSERT INTO ExampleTable VALUES();
            END",

            "CREATE VIEW ExampleView AS SELECT id, seq, content FROM ExampleTable WITH LOCAL CHECK OPTION",

            "CREATE DEFINER=`user1`@`%` PROCEDURE procedure1(IN arg1 int, IN arg2 int, OUT result int)
            LANGUAGE SQL
            NOT DETERMINISTIC
            READS SQL DATA
            COMMENT 'procedure comment'
            BEGIN
              SELECT 1;
            END",

            "CREATE DEFINER=`user1`@`%` FUNCTION function1(arg1 int, arg2 int)
            RETURNS int
            LANGUAGE SQL
            DETERMINISTIC
            READS SQL DATA
            COMMENT 'function comment'
            BEGIN
              RETURN arg1 + arg2;
            END",

            "CREATE FUNCTION altered_function(arg1 int, arg2 int)
            RETURNS int
            LANGUAGE SQL
            DETERMINISTIC
            READS SQL DATA
            SQL SECURITY INVOKER
            COMMENT 'function comment'
            BEGIN
              RETURN arg1 + arg2;
            END",

            "CREATE DEFINER=`user1`@`%` EVENT event1
            ON SCHEDULE EVERY 2 DAY STARTS '2022-01-01 00:00:01'
            ON COMPLETION PRESERVE
            ENABLE
            COMMENT 'event_trigger'
            DO BEGIN
               select sleep(2);
            END",

            "CREATE DEFINER=`user1`@`%` EVENT event2
            ON SCHEDULE EVERY 2 DAY STARTS '2022-01-01 00:00:01'
            ON COMPLETION PRESERVE
            ENABLE
            COMMENT 'event_trigger'
            DO BEGIN
               select sleep(2);
            END",
        ]));

        array_map(fn($sql) => $con2->executeStatement($sql), array_merge($dropSqls, [
            "CREATE TABLE NewTable (
              id INT(10) UNSIGNED NOT NULL,
              PRIMARY KEY (id)
            ) Comment='new_table' CHARSET=utf8mb4 COLLATE='utf8mb4_bin' ENGINE=InnoDB",

            "CREATE DEFINER=`user2`@`%` TRIGGER NewTable_trg AFTER INSERT ON NewTable FOR EACH ROW
            BEGIN
              INSERT INTO NewTable VALUES();
            END",

            "CREATE TABLE ExampleTable (
              id INT(10) UNSIGNED NOT NULL,
              name VARCHAR(64) NOT NULL,
              seq INT(10) UNSIGNED NOT NULL,
              pos INT(10) UNSIGNED NOT NULL,
              hashcode BINARY(40) NOT NULL,
              content LONGTEXT DEFAULT (_utf8mb4'I\'s') NOT NULL COLLATE utf8mb4_bin,
              bindata LONGBLOB NOT NULL,
              bitdata BIT(2) DEFAULT b'0' NOT NULL,
              enumdata ENUM('Y','N','D') NOT NULL,
              latlng GEOMETRY NOT NULL,
              jsondata JSON,
              json_id VARCHAR(64) AS (jsondata->'$.name') NOT NULL,
              create_at DATETIME(6) DEFAULT (NOW(3)),
              PRIMARY KEY (id),
              KEY contentx (content(200)),
              KEY fnx ((id * 3)),
              FULLTEXT KEY ftx (content) WITH PARSER ngram
            ) Comment='comment_to' CHARSET=utf8mb4 COLLATE='utf8mb4_bin' ROW_FORMAT=DYNAMIC",

            "CREATE DEFINER=`user2`@`%` TRIGGER ExampleTable_trg AFTER INSERT ON ExampleTable FOR EACH ROW
            BEGIN
              INSERT INTO ExampleTable VALUES();
            END",

            "CREATE VIEW ExampleView AS SELECT id, seq, name, content FROM ExampleTable WITH CASCADED CHECK OPTION",

            "CREATE DEFINER=`user2`@`%` FUNCTION function1(arg1 int, arg2 int, arg3 int)
            RETURNS int
            LANGUAGE SQL
            DETERMINISTIC
            READS SQL DATA
            COMMENT 'function comment-changed'
            BEGIN
              RETURN arg1 + arg2 + arg3;
            END",

            "CREATE DEFINER=`user2`@`%` PROCEDURE procedure2(IN arg int, OUT result int)
            LANGUAGE SQL
            NOT DETERMINISTIC
            READS SQL DATA
            COMMENT 'procedure comment-new'
            BEGIN
              SELECT 1;
            END",

            "CREATE FUNCTION altered_function(arg1 int, arg2 int)
            RETURNS int
            LANGUAGE SQL
            DETERMINISTIC
            READS SQL DATA
            SQL SECURITY DEFINER
            COMMENT 'function comment-altered'
            BEGIN
              RETURN arg1 + arg2;
            END",

            "CREATE DEFINER=`user2`@`%` EVENT event2
            ON SCHEDULE EVERY 3 DAY STARTS '2022-01-04 00:00:01'
            ON COMPLETION PRESERVE
            ENABLE
            COMMENT 'event_trigger-changed'
            DO BEGIN
               select sleep(3);
            END",

            "CREATE DEFINER=`user2`@`%` EVENT event3
            ON SCHEDULE EVERY 2 DAY STARTS '2022-01-01 00:00:01'
            ON COMPLETION PRESERVE
            ENABLE
            COMMENT 'event_trigger-new'
            DO BEGIN
               select sleep(3);
            END",
        ]));

        $comparator = $con1->createSchemaManager()->createComparator();

        $schema1 = clone $con1->createSchemaManager()->introspectSchema();
        $schema2 = clone $con2->createSchemaManager()->introspectSchema();

        $diff = $comparator->compareSchemas($schema1, $schema2);
        self::assertFalse($diff->isEmpty());

        $sqls   = $con1->getDatabasePlatform()->getAlterSchemaSQL($diff);
        $actual = implode("\n", $sqls);

        $expecteds = [
            // contains drop table
            "DROP TABLE DropTable",
            // contains new table
            "CREATE TABLE NewTable (id INT UNSIGNED NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_bin` ENGINE = InnoDB COMMENT = 'new_table' ROW_FORMAT = Dynamic",
            "CREATE DEFINER=`user2`@`%` TRIGGER NewTable_trg AFTER INSERT ON NewTable FOR EACH ROW",
            // contains alter table column
            "ADD name VARCHAR(64) NOT NULL AFTER id",
            "CHANGE pos pos INT UNSIGNED NOT NULL AFTER seq",
            '-- length: 32 => 40',
            "CHANGE hashcode hashcode BINARY(40) NOT NULL AFTER pos",
            "CHANGE content content LONGTEXT DEFAULT (_utf8mb4'I\'s') NOT NULL AFTER hashcode",
            "CHANGE bindata bindata LONGBLOB NOT NULL AFTER content",
            "CHANGE bitdata bitdata BIT(2) DEFAULT b'0' NOT NULL AFTER bindata",
            "CHANGE enumdata enumdata ENUM('Y','N','D') NOT NULL AFTER bitdata",
            "CHANGE latlng latlng GEOMETRY NOT NULL AFTER enumdata",
            '-- type: integer => string',
            '-- notnull: false => true',
            '-- generation.expression: "json_extract',
            "CHANGE json_id json_id VARCHAR(64) AS (json_extract(`jsondata`,_utf8mb4'$.name')) VIRTUAL NOT NULL",
            "CHANGE create_at create_at DATETIME(6) DEFAULT (now(3))",
            // contains alter table option
            '-- collation: "utf8mb4_general_ci" => "utf8mb4_bin"',
            '-- comment: "comment_from" => "comment_to"',
            '-- row_format: "Compressed" => "Dynamic"',
            "COLLATE `utf8mb4_bin` COMMENT = 'comment_to' ROW_FORMAT = Dynamic",
            // contains alter index
            "DROP INDEX contentx ON ExampleTable",
            "CREATE INDEX contentx ON ExampleTable (content(200))",
            "DROP INDEX fnx ON ExampleTable",
            "CREATE INDEX fnx ON ExampleTable (((`id` * 3)))",
            "DROP INDEX ftx ON ExampleTable",
            "CREATE FULLTEXT INDEX ftx ON ExampleTable (content) WITH PARSER ngram",
            // contains alter table trigger
            '-- options.timing: "BEFORE" => "AFTER"',
            'LOCK TABLES ExampleTable',
            "DROP TRIGGER IF EXISTS ExampleTable_trg",
            "CREATE DEFINER=`user2`@`%` TRIGGER ExampleTable_trg AFTER INSERT ON ExampleTable FOR EACH ROW",
            'UNLOCK TABLES',
            // contains alter view
            '-- sql: "',
            '-- options.checkOption: "LOCAL" => "CASCADED"',
            "ALTER VIEW ExampleView AS",
            "WITH CASCADED CHECK OPTION",
            // contains drop procedure
            "DROP PROCEDURE procedure1",
            // contains new procedure
            "CREATE DEFINER=`user2`@`%` PROCEDURE procedure2(IN arg int, OUT result int)",
            // contains recreate procedure
            '-- options.comment: "function comment" => "function comment-changed"',
            "DROP FUNCTION function1",
            "CREATE DEFINER=`user2`@`%` FUNCTION function1(arg1 int, arg2 int, arg3 int)",
            "COMMENT 'function comment-changed'",
            "RETURN arg1 + arg2 + arg3;",
            // contains alter procedure
            '-- options.comment: "function comment" => "function comment-altered"',
            '-- options.securityType: "INVOKER" => "DEFINER"',
            "ALTER FUNCTION altered_function",
            "COMMENT 'function comment-altered'",
            // contains drop event
            "DROP EVENT event1",
            // contains new event
            "CREATE DEFINER=`user2`@`%` EVENT event3",
            // contains alter event
            '-- options.intervalValue: "2" => "3"',
            '-- options.comment: "event_trigger" => "event_trigger-changed"',
            "ALTER DEFINER=`user2`@`%` EVENT event2",
            "STARTS '2022-01-04 00:00:01'",
            "COMMENT 'event_trigger-changed'",
        ];

        foreach ($expecteds as $expected) {
            self::assertStringContainsString($expected, $actual);
        }

        $unexpecteds = [
            // only position changed
            "CHANGE seq seq",
        ];

        foreach ($unexpecteds as $unexpected) {
            self::assertStringNotContainsString($unexpected, $actual);
        }

        foreach ($sqls as $sql) {
            $con1->executeStatement($sql);
        }

        $schema1 = $con1->createSchemaManager()->introspectSchema();
        $schema2 = $con2->createSchemaManager()->introspectSchema();

        $diff = $comparator->compareSchemas($schema1, $schema2);
        self::assertTrue($diff->isEmpty());

        $sqls = $con1->getDatabasePlatform()->getAlterSchemaSQL($diff);
        self::assertEmpty($sqls);

        array_map(fn($sql) => $con1->executeStatement($sql), $dropSqls);
        array_map(fn($sql) => $con2->executeStatement($sql), $dropSqls);
    }

    public function test_postgres(): void
    {
        if (! $this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->markTestSkipped();
        }

        $con1 = $this->connection;
        $con2 = TestUtil::getPrivilegedConnection();

        $dropSql = <<<SQL
            DROP TABLE     IF EXISTS NewTable;
            DROP TABLE     IF EXISTS DropTable;
            DROP TABLE     IF EXISTS ExampleTable;
            DROP TABLE     IF EXISTS ExampleTable;
            DROP PROCEDURE IF EXISTS procedure1;
            DROP PROCEDURE IF EXISTS procedure2;
            DROP FUNCTION  IF EXISTS function1;
            DROP FUNCTION  IF EXISTS altered_function;
            DROP FUNCTION  IF EXISTS triggered_function;
        SQL;

        $con1->executeStatement(<<<DDL
            $dropSql
            
            CREATE TABLE ExampleTable (
              id INT NOT NULL,
              seq INT NOT NULL,
              content TEXT NOT NULL,
              PRIMARY KEY (id)
            );

            CREATE PROCEDURE procedure1(IN arg1 int, IN arg2 int, OUT result int) AS $$
            BEGIN
              SELECT 1;
            END;
            $$
            LANGUAGE PLPGSQL;
            
            CREATE FUNCTION function1(arg1 int, arg2 int) RETURNS int AS $$
            BEGIN
              RETURN arg1 + arg2;
            END;
            $$
            LANGUAGE PLPGSQL;
            
            CREATE FUNCTION altered_function(arg1 int, arg2 int) RETURNS int AS $$
            BEGIN
              RETURN arg1 + arg2;
            END;
            $$
            LANGUAGE PLPGSQL
            IMMUTABLE
            RETURNS NULL ON NULL INPUT;
            
            CREATE FUNCTION triggered_function() RETURNS trigger AS $$
            BEGIN
              RETURN NEW;
            END;
            $$
            LANGUAGE PLPGSQL;

            CREATE TRIGGER ExampleTable_trg BEFORE INSERT ON ExampleTable FOR EACH ROW EXECUTE PROCEDURE triggered_function();
            DDL
        );

        $con2->executeStatement(<<<DDL
            $dropSql
            
            CREATE TABLE ExampleTable (
              id INT NOT NULL,
              seq INT NOT NULL,
              content TEXT NOT NULL,
              PRIMARY KEY (id)
            );
            
            CREATE FUNCTION function1(arg1 int, arg2 int, arg3 int) RETURNS int AS $$
            BEGIN
              RETURN arg1 + arg2 + arg3;
            END;
            $$
            LANGUAGE PLPGSQL;

            CREATE PROCEDURE procedure2(IN arg int, OUT result int) AS $$
            SELECT arg + 123
            $$
            LANGUAGE SQL;
            
            CREATE FUNCTION altered_function(arg1 int, arg2 int) RETURNS int AS $$
            BEGIN
              RETURN arg1 + arg2;
            END;
            $$
            LANGUAGE PLPGSQL
            VOLATILE
            CALLED ON NULL INPUT;
            
            CREATE FUNCTION triggered_function() RETURNS trigger AS $$
            BEGIN
              RETURN NEW;
            END;
            $$
            LANGUAGE PLPGSQL;
            
            CREATE TRIGGER ExampleTable_trg BEFORE INSERT ON ExampleTable FOR EACH STATEMENT EXECUTE PROCEDURE triggered_function();
            DDL
        );

        $con1->close();
        $con2->close();

        $comparator = $con1->createSchemaManager()->createComparator();

        $schema1 = clone $con1->createSchemaManager()->introspectSchema();
        $schema2 = clone $con2->createSchemaManager()->introspectSchema();

        $diff = $comparator->compareSchemas($schema1, $schema2);
        self::assertFalse($diff->isEmpty());

        $sqls   = $con1->getDatabasePlatform()->getAlterSchemaSQL($diff);
        $actual = implode("\n", $sqls);

        $expecteds = [
            // contains alter table trigger
            "DROP TRIGGER IF EXISTS exampletable_trg ON exampletable",
            "CREATE TRIGGER exampletable_trg BEFORE INSERT ON exampletable FOR EACH STATEMENT EXECUTE FUNCTION triggered_function()",
            // contains drop procedure
            "DROP PROCEDURE procedure1(IN arg1 int4, IN arg2 int4, OUT result int4)",
            // contains new procedure
            "CREATE PROCEDURE procedure2(IN arg int4, OUT result int4)",
            // contains recreate procedure
            "DROP FUNCTION function1(IN arg1 int4, IN arg2 int4)",
            "RETURN arg1 + arg2 + arg3;",
            // contains alter procedure
            "ALTER FUNCTION altered_function",
        ];

        foreach ($expecteds as $expected) {
            self::assertStringContainsString($expected, $actual);
        }

        $unexpecteds = [
            // only position changed
            "CHANGE seq seq",
        ];

        foreach ($unexpecteds as $unexpected) {
            self::assertStringNotContainsString($unexpected, $actual);
        }

        foreach ($sqls as $sql) {
            $con1->executeStatement($sql);
        }

        $schema1 = $con1->createSchemaManager()->introspectSchema();
        $schema2 = $con2->createSchemaManager()->introspectSchema();

        $diff = $comparator->compareSchemas($schema1, $schema2);
        self::assertTrue($diff->isEmpty());

        $sqls = $con1->getDatabasePlatform()->getAlterSchemaSQL($diff);
        self::assertEmpty($sqls);

        $con1->executeStatement($dropSql);
        $con2->executeStatement($dropSql);

        $con1->close();
        $con2->close();
    }
}
