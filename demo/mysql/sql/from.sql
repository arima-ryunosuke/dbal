DROP DATABASE IF EXISTS test_demo_from;
CREATE DATABASE test_demo_from;

USE test_demo_from;

CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  content TEXT NOT NULL COLLATE utf8_bin,
  PRIMARY KEY (id),
  INDEX INDEX1 (seq)
) Comment='comment_from' CHARSET=utf8 COLLATE='utf8_general_ci' ENGINE=MyISAM;

CREATE VIEW ExampleView AS SELECT id, seq, content FROM ExampleTable;

CREATE TRIGGER ExampleTable_trg BEFORE INSERT ON ExampleTable FOR EACH ROW
  INSERT INTO ExampleTable VALUES() -- dummy statement
;
