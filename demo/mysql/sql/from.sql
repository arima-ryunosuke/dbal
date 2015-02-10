DROP DATABASE IF EXISTS test_demo_from;
CREATE DATABASE test_demo_from;

USE test_demo_from;

CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  content TEXT NOT NULL COLLATE utf8_bin,
  PRIMARY KEY (id),
  INDEX INDEX1 (seq)
) Comment='comment_from' ROW_FORMAT=DYNAMIC CHARSET=utf8 COLLATE='utf8_general_ci' ENGINE=InnoDB;
