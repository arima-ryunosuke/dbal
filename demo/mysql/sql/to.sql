DROP DATABASE IF EXISTS test_demo_to;
CREATE DATABASE test_demo_to;

USE test_demo_to;

CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  content MEDIUMTEXT NOT NULL COLLATE utf8_bin,
  `point` POINT NOT NULL,
  `linestring` LINESTRING NOT NULL,
  `polygon` POLYGON NOT NULL,
  `geometry` GEOMETRY NOT NULL,
  `multipoint` MULTIPOINT NOT NULL,
  `multilinestring` MULTILINESTRING NOT NULL,
  `multipolygon` MULTIPOLYGON NOT NULL,
  `geometrycollection` GEOMETRYCOLLECTION NOT NULL,
  PRIMARY KEY (id),
  INDEX INDEX2 (seq)
) Comment='comment_to' ROW_FORMAT=DYNAMIC CHARSET=utf8 COLLATE='utf8_bin' ENGINE=MyISAM;
