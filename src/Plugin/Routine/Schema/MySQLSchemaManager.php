<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Routine;

trait MySQLSchemaManager
{
    protected function _getPortableRoutineDefinition(array $routine): Routine
    {
        $routine['deterministic'] = filter_var($routine['deterministic'], FILTER_VALIDATE_BOOLEAN);

        $routine['parameters'] = json_decode($routine['parameters'], true);
        unset($routine['parameters']['']);

        return parent::_getPortableRoutineDefinition($routine);
    }

    protected function selectRoutines(string $databaseName): Result
    {
        $sql = <<<SQL
            SELECT
                r.ROUTINE_NAME            AS `name`,
                MIN(r.ROUTINE_TYPE)       AS `type`,
                MIN(r.DATA_TYPE)          AS `returnType`,
                MIN(r.DTD_IDENTIFIER)     AS `returnTypeDeclaration`,
                MIN(r.IS_DETERMINISTIC)   AS `deterministic`,
                MIN(r.SQL_DATA_ACCESS)    AS `dataAccess`,
                MIN(r.ROUTINE_BODY)       AS `language`,
                MIN(r.ROUTINE_DEFINITION) AS `statement`,
                MIN(r.SECURITY_TYPE)      AS `securityType`,
                MIN(r.DEFINER)            AS `definer`,
                MIN(r.ROUTINE_COMMENT)    AS `comment`,
                JSON_OBJECTAGG(IFNULL(p.PARAMETER_NAME, ""), JSON_OBJECT(
                    "mode",            p.PARAMETER_MODE,
                    "type",            p.DATA_TYPE,
                    "typeDeclaration", p.DTD_IDENTIFIER
                )) AS `parameters`
            FROM information_schema.ROUTINES r
            LEFT JOIN information_schema.PARAMETERS p
                ON r.ROUTINE_NAME = p.SPECIFIC_NAME AND p.ORDINAL_POSITION > 0 -- 0 is return type
            WHERE ROUTINE_SCHEMA = ?
            GROUP BY r.ROUTINE_NAME
            ORDER BY r.ROUTINE_NAME
        SQL;

        return $this->connection->executeQuery($sql, [$databaseName]);
    }
}
