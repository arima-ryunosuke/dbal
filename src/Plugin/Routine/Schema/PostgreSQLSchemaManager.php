<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Plugin\Routine\Schema\Exception\RoutineDoesNotExist;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Routine;

/**
 * @codeCoverageIgnore this is implemented to broaden one's horizons
 */
trait PostgreSQLSchemaManager
{
    protected function _getPortableRoutineDefinition(array $routine): Routine
    {
        $routine['deterministic'] = filter_var($routine['deterministic'], FILTER_VALIDATE_BOOLEAN);
        $routine['nullcall']      = filter_var($routine['nullcall'], FILTER_VALIDATE_BOOLEAN);

        $routine['parameters'] = json_decode($routine['parameters'], true);
        unset($routine['parameters']['']);

        return parent::_getPortableRoutineDefinition($routine);
    }

    protected function selectRoutines(string $databaseName): Result
    {
        $sql = <<<SQL
            SELECT
                r.routine_name            AS "name",
                MIN(r.routine_type)       AS "type",
                MIN(r.data_type)          AS "returnType",
                MIN(r.type_udt_name)      AS "returnTypeDeclaration",
                MIN(r.is_deterministic)   AS "deterministic",
                MIN(r.is_null_call)       AS "nullcall",
                MIN(r.external_language)  AS "language",
                MIN(r.routine_definition) AS "statement",
                json_object_agg(COALESCE(p.parameter_name, CAST(p.ordinal_position as char), ''), json_build_object(
                    'mode',            p.parameter_mode,
                    'type',            p.data_type,
                    'typeDeclaration', p.udt_name
                ) ORDER BY p.ordinal_position) as parameters
            FROM information_schema.routines r
            LEFT JOIN information_schema.parameters p
                ON r.specific_name = p.specific_name
            WHERE r.routine_catalog = ?
                AND r.routine_schema NOT LIKE 'pg\_%'
                AND r.routine_schema != 'information_schema'
            GROUP BY r.routine_name
            ORDER BY r.routine_name
        SQL;

        return $this->connection->executeQuery($sql, [$databaseName]);
    }

    protected function _getPortableRoutineList(array $routines): array
    {
        $list = array_filter(array_map(fn($value) => $this->_getPortableRoutineDefinition($value), $routines));

        // postgresql is overloadable and cannot return in an associative array
        return array_values($list);
    }

    public function dropRoutine(string $name, string $type, array $parameters = []): void
    {
        // postgresql is overloadable and cannot return in an associative array
        $matches = array_filter($this->listRoutines(), function (Routine $routine) use ($name, $parameters) {
            if ($routine->getName() === $name) {
                if ($routine->getOption('parameters') === $parameters) {
                    return true;
                }
            }
        });
        $routine = reset($matches) ?: null; //throw RoutineDoesNotExist::new($name);
        if (! $routine) {
            throw RoutineDoesNotExist::new($name);
        }
        /** @see AbstractPlatform::getDropProcedureSQL(), AbstractPlatform::getDropFunctionSQL() */
        $this->connection->executeStatement(
            $this->platform->{"getDrop{$routine->getType()}SQL"}($name, $routine->getOption('parameters')),
        );
    }
}
