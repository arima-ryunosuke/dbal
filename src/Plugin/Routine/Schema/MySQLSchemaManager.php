<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Result;

trait MySQLSchemaManager
{
    protected function _getPortableRoutineDefinition($routine)
    {
        $routine['deterministic'] = filter_var($routine['deterministic'], FILTER_VALIDATE_BOOLEAN);

        // for mysql >= 8
        // $routine['parameters'] = json_decode($routine['parameters'], true);
        // unset($routine['parameters']['']);

        // for mysql < 8
        $routine['parameters'] = (function (&$routine) {
            $params = [];
            foreach (['name', 'type', 'typeDeclaration', 'mode'] as $key) {
                $params[$key] = preg_split("#\t#", $routine["parameter_$key"], -1, PREG_SPLIT_NO_EMPTY);
                unset($routine["parameter_$key"]);
            }

            $names = $params['name'];
            unset($params['name']);
            $keys = array_keys($params);

            $parameters = [];
            foreach ($names as $i => $name) {
                $parameters[$name] = array_combine($keys, array_column($params, $i));
            }
            return $parameters;
        })($routine);

        return parent::_getPortableRoutineDefinition($routine);
    }

    protected function selectRoutines(string $databaseName): Result
    {
        // for mysql >= 8
        // $parameters = <<<PARAMS
        // JSON_OBJECTAGG(IFNULL(p.PARAMETER_NAME, ""), JSON_OBJECT(
        //     "mode",            p.PARAMETER_MODE,
        //     "type",            p.DATA_TYPE,
        //     "typeDeclaration", p.DTD_IDENTIFIER
        // ))
        // PARAMS;

        // for mysql < 8
        $parameters = [
            'parameter_name'            => "GROUP_CONCAT(IFNULL(p.PARAMETER_NAME, '')   ORDER BY p.ORDINAL_POSITION SEPARATOR '\t')",
            'parameter_mode'            => "GROUP_CONCAT(IFNULL(p.PARAMETER_MODE, '')   ORDER BY p.ORDINAL_POSITION SEPARATOR '\t')",
            'parameter_type'            => "GROUP_CONCAT(IFNULL(p.DATA_TYPE, '')        ORDER BY p.ORDINAL_POSITION SEPARATOR '\t')",
            'parameter_typeDeclaration' => "GROUP_CONCAT(IFNULL(p.DTD_IDENTIFIER, '')   ORDER BY p.ORDINAL_POSITION SEPARATOR '\t')",
        ];
        $parameters = implode(",\n", array_map(fn($column, $alias) => "$column AS `$alias`", $parameters, array_keys($parameters)));

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
                $parameters
            FROM information_schema.ROUTINES r
            LEFT JOIN information_schema.PARAMETERS p
                ON r.ROUTINE_SCHEMA = p.SPECIFIC_SCHEMA AND r.ROUTINE_NAME = p.SPECIFIC_NAME AND p.ORDINAL_POSITION > 0 -- 0 is return type
            WHERE ROUTINE_SCHEMA = ?
            GROUP BY r.ROUTINE_NAME
            ORDER BY r.ROUTINE_NAME
        SQL;

        return $this->_conn->executeQuery($sql, [$databaseName]);
    }
}
