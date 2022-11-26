<?php

namespace Doctrine\DBAL\Plugin\Routine\Platforms;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\SchemaDiff;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterSchemaSQL()
     */
    protected function interceptGetAlterSchemaSQLByRoutine(array $sql, SchemaDiff $diff): array
    {
        foreach ($diff->createdProcedures as $routine) {
            $sql[] = $this->getCreateProcedureSQL($routine);
        }

        foreach ($diff->createdFunctions as $routine) {
            $sql[] = $this->getCreateFunctionSQL($routine);
        }

        foreach ($diff->droppedProcedures as $routine) {
            $sql[] = $this->getDropProcedureSQL($routine->getLocalName(), $routine->getOption('parameters'));
        }

        foreach ($diff->droppedFunctions as $routine) {
            $sql[] = $this->getDropFunctionSQL($routine->getLocalName(), $routine->getOption('parameters'));
        }

        foreach ($diff->alteredProcedures as $n => $routine) {
            $alteredSqls       = $this->getAlterProcedureSQL($routine);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $this->buildDiffComment($diff->procedureDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        foreach ($diff->alteredFunctions as $n => $routine) {
            $alteredSqls       = $this->getAlterFunctionSQL($routine);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $this->buildDiffComment($diff->functionDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        return ['sql' => $sql];
    }

    public function supportsRoutines(): bool
    {
        return false;
    }

    public function getCreateProcedureSQL(Routine $routine): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getCreateFunctionSQL(Routine $routine): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getDropProcedureSQL(string $routine, array $parameters = []): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getDropFunctionSQL(string $routine, array $parameters = []): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getAlterProcedureSQL(Routine $routine): array
    {
        return [
            $this->getDropProcedureSQL($routine->getLocalName(), $routine->getOption('old')->getOption('parameters')),
            $this->getCreateProcedureSQL($routine),
        ];
    }

    public function getAlterFunctionSQL(Routine $routine): array
    {
        return [
            $this->getDropFunctionSQL($routine->getLocalName(), $routine->getOption('old')->getOption('parameters')),
            $this->getCreateFunctionSQL($routine),
        ];
    }

    protected function _getDiffKeys(Routine $new, Routine $old): array
    {
        $newattrs = ['statement' => $new->getStatement()] + $new->getOptions();
        $oldattrs = ['statement' => $old->getStatement()] + $old->getOptions();

        $diffkeys = [];
        foreach ($oldattrs as $key => $value) {
            if (! array_key_exists($key, $newattrs) || $newattrs[$key] !== $value) {
                $diffkeys[] = $key;
            }
        }
        return $diffkeys;
    }
}
