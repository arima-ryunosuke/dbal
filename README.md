# Doctrine DBAL

Powerful database abstraction layer with many features for database schema introspection, schema management and PDO abstraction.

* Master: [![Build Status](https://secure.travis-ci.org/doctrine/dbal.png?branch=master)](http://travis-ci.org/doctrine/dbal) [![Dependency Status](https://www.versioneye.com/php/doctrine:dbal/dev-master/badge.png)](https://www.versioneye.com/php/doctrine:dbal/dev-master)
* 2.4: [![Build Status](https://secure.travis-ci.org/doctrine/dbal.png?branch=2.4)](http://travis-ci.org/doctrine/dbal) [![Dependency Status](https://www.versioneye.com/php/doctrine:dbal/2.4.2/badge.png)](https://www.versioneye.com/php/doctrine:dbal/2.4.2)
* 2.3: [![Build Status](https://secure.travis-ci.org/doctrine/dbal.png?branch=2.3)](http://travis-ci.org/doctrine/dbal) [![Dependency Status](https://www.versioneye.com/php/doctrine:dbal/2.3.4/badge.png)](https://www.versioneye.com/php/doctrine:dbal/2.3.4)

## More resources:

* [Website](http://www.doctrine-project.org/projects/dbal.html)
* [Documentation](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/)
* [Issue Tracker](http://www.doctrine-project.org/jira/browse/DBAL)
* [Downloads](http://github.com/doctrine/dbal/downloads)

## Forked

from [doctrine/dbal](https://github.com/doctrine/dbal).

This forked repository has specialized mysql when comparing.

- support TINYTEXT / TEXT / MEDIUMTEXT / LONGTEXT types when alter column type
- support FIRST / AFTER suffix when add column
- support TABLE OPTION (e.g. ROW_FORMAT, ENGINE etc) when create table, alter table
- support SPATIAL types (e.g. POINT, GEOMETRY etc)
- support Compare view
- fix UPPERCASE index name when rename index

### example:

```sql
-- from table
CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  content TEXT NOT NULL COLLATE utf8_bin,
  PRIMARY KEY (id),
  INDEX INDEX1 (seq)
) Comment='comment_from' CHARSET=utf8 COLLATE='utf8_general_ci' ENGINE=InnoDB;

-- to table
CREATE TABLE ExampleTable (
  id INT(10) UNSIGNED NOT NULL,
  seq INT(10) UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  content MEDIUMTEXT NOT NULL COLLATE utf8_bin,
  PRIMARY KEY (id),
  INDEX INDEX2 (seq)
) Comment='comment_to' CHARSET=utf8 COLLATE='utf8_bin' ENGINE=MyISAM;
```

```sql
-- original comparing
ALTER TABLE ExampleTable
  ADD name VARCHAR(64) NOT NULL COLLATE utf8_bin
DROP INDEX index1 ON ExampleTable
CREATE INDEX INDEX2 ON ExampleTable (
  seq
)

-- forked comparing
ALTER TABLE ExampleTable
  ADD name VARCHAR(64) NOT NULL COLLATE utf8_bin AFTER seq,
  CHANGE content content MEDIUMTEXT NOT NULL COLLATE utf8_bin,
  ENGINE MyISAM COLLATE utf8_bin COMMENT 'comment_to'
DROP INDEX INDEX1 ON ExampleTable
CREATE INDEX INDEX2 ON ExampleTable (
  seq
)
```
