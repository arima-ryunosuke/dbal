<?php

namespace Doctrine\Tests\DBAL;

use Doctrine\DBAL\Platforms\MySQLPlatform;
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

        $con1->executeStatement("
DROP TABLE IF EXISTS ExampleTable;
DROP VIEW  IF EXISTS ExampleView;

CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  hashcode BINARY(32) NOT NULL,
  content TEXT NOT NULL COLLATE utf8_bin,
  bindata BLOB NOT NULL,
  PRIMARY KEY (id)
) Comment='comment_from' CHARSET=utf8 COLLATE='utf8_general_ci' ENGINE=MyISAM;

CREATE VIEW ExampleView AS SELECT id, seq, content FROM ExampleTable;

CREATE TRIGGER ExampleTable_trg BEFORE INSERT ON ExampleTable FOR EACH ROW
  INSERT INTO ExampleTable VALUES() -- dummy statement
;
");

        $con2->executeStatement("
DROP TABLE IF EXISTS ExampleTable;
DROP VIEW  IF EXISTS ExampleView;

CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  hashcode BINARY(40) NOT NULL,
  name VARCHAR(64) NOT NULL,
  content MEDIUMTEXT NOT NULL COLLATE utf8_bin,
  bindata MEDIUMBLOB NOT NULL,
  PRIMARY KEY (id)
) Comment='comment_to' CHARSET=utf8 COLLATE='utf8_bin' ENGINE=InnoDB;

CREATE VIEW ExampleView AS SELECT id, seq, name, content FROM ExampleTable;

CREATE TRIGGER ExampleTable_trg AFTER INSERT ON ExampleTable FOR EACH ROW
  INSERT INTO ExampleTable VALUES() -- dummy statement
;");

        $schema1 = $con1->createSchemaManager()->createSchema();
        $schema2 = $con2->createSchemaManager()->createSchema();
        $sqls = implode("\n", $schema1->getMigrateToSql($schema2, $con1->getDatabasePlatform()));

        $expecteds = [
            // contains alter table column
            "ADD name VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_bin` AFTER hashcode",
            "CHANGE hashcode hashcode BINARY(40) NOT NULL AFTER seq",
            "CHANGE content content MEDIUMTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_bin` AFTER name",
            "CHANGE bindata bindata MEDIUMBLOB NOT NULL AFTER content",
            // contains alter table option
            "DEFAULT CHARACTER SET utf8 COLLATE `utf8_bin` ENGINE = InnoDB COMMENT = 'comment_to'",
            // contains alter table trigger
            "DROP TRIGGER ExampleTable_trg",
            "CREATE TRIGGER ExampleTable_trg AFTER INSERT ON ExampleTable FOR EACH ROW",
            // contains alter view
            "CREATE OR REPLACE VIEW ExampleView AS",
        ];

        foreach ($expecteds as $expected) {
            self::assertStringContainsString($expected, $sqls);
        }
    }
}
