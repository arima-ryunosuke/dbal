DROP DATABASE IF EXISTS test_demo_to;
CREATE DATABASE test_demo_to;

USE test_demo_to;

CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  content MEDIUMTEXT NOT NULL COLLATE utf8_bin,
  mypoint POINT NOT NULL,
  PRIMARY KEY (id),
  INDEX INDEX2 (seq),
  INDEX INDEX3 (name)
) Comment='comment_to' CHARSET=utf8 COLLATE='utf8_bin' ENGINE=InnoDB;

CREATE VIEW ExampleView AS SELECT id, seq, name, content FROM ExampleTable;
