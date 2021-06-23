<?php

namespace Doctrine\Tests\DBAL;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Tests\TestUtil;
use PHPUnit\Framework\TestCase;

final class RyunosukeTest extends TestCase
{
    public function test_integrate(): void
    {
        $con1 = TestUtil::getConnection();
        $con2 = TestUtil::getPrivilegedConnection();

        if (!($con1->getDatabasePlatform() instanceof MySQLPlatform && $con2->getDatabasePlatform() instanceof MySQLPlatform)) {
            self::markTestSkipped('Mysql only test');
        }

        $con1->executeStatement(<<<SQL
            DROP TABLE IF EXISTS DropTable;
            DROP TABLE IF EXISTS ExampleTable;
            DROP VIEW  IF EXISTS ExampleView;
            
            CREATE TABLE DropTable (
              id INT(10) UNSIGNED NOT NULL,
              PRIMARY KEY (id)
            );
            
            CREATE TABLE ExampleTable (
              id INT(10) UNSIGNED NOT NULL,
              seq INT(10) UNSIGNED NOT NULL,
              hashcode BINARY(32) NOT NULL,
              content TEXT NOT NULL COLLATE utf8_bin,
              bindata BLOB NOT NULL,
              jsondata JSON,
              json_id INT(10) AS (jsondata->'$.id'),
              PRIMARY KEY (id),
              FULLTEXT KEY ftx (content)
            ) Comment='comment_from' CHARSET=utf8 COLLATE='utf8_general_ci' ENGINE=MyISAM;
            
            CREATE VIEW ExampleView AS SELECT id, seq, content FROM ExampleTable;
            SQL
        );
        $con1->executeStatement(<<<SQL
            CREATE TRIGGER ExampleTable_trg BEFORE INSERT ON ExampleTable FOR EACH ROW
              INSERT INTO ExampleTable VALUES() -- dummy statement
            SQL
        );

        $con2->executeStatement(<<<SQL
            DROP TABLE IF EXISTS NewTable;
            DROP TABLE IF EXISTS ExampleTable;
            DROP VIEW  IF EXISTS ExampleView;
            
            CREATE TABLE NewTable (
              id INT(10) UNSIGNED NOT NULL,
              PRIMARY KEY (id)
            ) CHARSET=utf8 COLLATE='utf8_bin' ENGINE=InnoDB;
            
            CREATE TABLE ExampleTable (
              id INT(10) UNSIGNED NOT NULL,
              seq INT(10) UNSIGNED NOT NULL,
              hashcode BINARY(40) NOT NULL,
              name VARCHAR(64) NOT NULL,
              content MEDIUMTEXT NOT NULL COLLATE utf8_bin,
              bindata MEDIUMBLOB NOT NULL,
              jsondata JSON,
              json_id VARCHAR(64) AS (jsondata->'$.name') NOT NULL,
              PRIMARY KEY (id),
              FULLTEXT KEY ftx (content) WITH PARSER ngram
            ) Comment='comment_to' CHARSET=utf8 COLLATE='utf8_bin' ENGINE=InnoDB;
            
            CREATE VIEW ExampleView AS SELECT id, seq, name, content FROM ExampleTable;
            SQL
        );
        $con2->executeStatement(<<<SQL
            CREATE TRIGGER NewTable_trg AFTER INSERT ON NewTable FOR EACH ROW
              INSERT INTO NewTable VALUES() -- dummy statement
            SQL
        );
        $con2->executeStatement(<<<SQL
            CREATE TRIGGER ExampleTable_trg AFTER INSERT ON ExampleTable FOR EACH ROW
              INSERT INTO ExampleTable VALUES() -- dummy statement
            SQL
        );

        $comparator = new Comparator();

        $schema1 = $con1->createSchemaManager()->createSchema();
        $schema2 = $con2->createSchemaManager()->createSchema();
        $diff = $comparator->compareSchemas($schema1, $schema2);
        $sqls = $diff->toSql($con1->getDatabasePlatform());
        $actual = implode("\n", $sqls);

        $expecteds = [
            // contains drop table
            "DROP TABLE DropTable",
            // contains new table
            "CREATE TABLE NewTable (id INT UNSIGNED NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_bin` ENGINE = InnoDB",
            "CREATE TRIGGER NewTable_trg AFTER INSERT ON NewTable FOR EACH ROW",
            // contains alter table column
            "ADD name VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_bin` AFTER hashcode",
            "CHANGE hashcode hashcode BINARY(40) NOT NULL AFTER seq",
            "CHANGE content content MEDIUMTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_bin` AFTER name",
            "CHANGE bindata bindata MEDIUMBLOB NOT NULL AFTER content",
            "CHANGE json_id json_id VARCHAR(64) AS (json_extract(`jsondata`,_utf8mb4'$.name')) VIRTUAL NOT NULL",
            // contains alter table option
            "DEFAULT CHARACTER SET utf8 COLLATE `utf8_bin` ENGINE = InnoDB COMMENT = 'comment_to'",
            // contains alter index
            "DROP INDEX ftx ON ExampleTable",
            "CREATE FULLTEXT INDEX ftx ON ExampleTable (content) WITH PARSER ngram",
            // contains alter table trigger
            "DROP TRIGGER ExampleTable_trg",
            "CREATE TRIGGER ExampleTable_trg AFTER INSERT ON ExampleTable FOR EACH ROW",
            // contains alter view
            "CREATE OR REPLACE VIEW ExampleView AS",
        ];

        foreach ($expecteds as $expected) {
            self::assertStringContainsString($expected, $actual);
        }
        foreach ($sqls as $sql) {
            $con1->executeStatement($sql);
        }

        $schema1 = $con1->createSchemaManager()->createSchema();
        $schema2 = $con2->createSchemaManager()->createSchema();
        $diff = $comparator->compareSchemas($schema1, $schema2);
        self::assertEmpty($diff->toSql($con1->getDatabasePlatform()));
    }
}
