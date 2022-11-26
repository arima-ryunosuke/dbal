<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

use Doctrine\DBAL\Result;

trait PostgreSQLSchemaManager
{
    /**
     * @codeCoverageIgnore
     * @see self::selectTableColumns()
     */
    protected function selectViewColumns(string $databaseName, ?string $viewName): Result
    {
        $sql = 'SELECT ';

        if ($viewName === null) {
            $sql .= 'c.relname AS table_name, n.nspname AS schema_name,';
        }

        $sql .= sprintf(<<<'SQL'
            a.attnum,
            quote_ident(a.attname) AS field,
            t.typname AS type,
            format_type(a.atttypid, a.atttypmod) AS complete_type,
            (SELECT tc.collcollate FROM pg_catalog.pg_collation tc WHERE tc.oid = a.attcollation) AS collation,
            (SELECT t1.typname FROM pg_catalog.pg_type t1 WHERE t1.oid = t.typbasetype) AS domain_type,
            (SELECT format_type(t2.typbasetype, t2.typtypmod) FROM
              pg_catalog.pg_type t2 WHERE t2.typtype = 'd' AND t2.oid = a.atttypid) AS domain_complete_type,
            a.attnotnull AS isnotnull,
            a.attidentity,
            (SELECT 't'
             FROM pg_index
             WHERE c.oid = pg_index.indrelid
                AND pg_index.indkey[0] = a.attnum
                AND pg_index.indisprimary = 't'
            ) AS pri,
            (%s) AS default,
            (SELECT pg_description.description
                FROM pg_description WHERE pg_description.objoid = c.oid AND a.attnum = pg_description.objsubid
            ) AS comment
            FROM pg_attribute a
                INNER JOIN pg_class c
                    ON c.oid = a.attrelid
                INNER JOIN pg_type t
                    ON t.oid = a.atttypid
                INNER JOIN pg_namespace n
                    ON n.oid = c.relnamespace
                LEFT JOIN pg_depend d
                    ON d.objid = c.oid
                        AND d.deptype = 'e'
                        AND d.classid = (SELECT oid FROM pg_class WHERE relname = 'pg_class')
            SQL, $this->platform->getDefaultColumnValueSQLSnippet());

        $conditions = array_merge([
            'a.attnum > 0',
            "c.relkind = 'v'",
            'd.refobjid IS NULL',
        ], $this->buildQueryConditions($viewName));

        $sql .= ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY a.attnum';

        return $this->connection->executeQuery($sql);
    }
}
