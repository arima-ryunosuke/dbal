<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Routine;

trait SchemaDiff
{
    /** @var Routine[] All created routine */
    public $createdProcedures = [], $createdFunctions = [];

    /** @var Routine[] All dropped routine */
    public $droppedProcedures = [], $droppedFunctions = [];

    /** @var Routine[] All altered routine */
    public $alteredProcedures = [], $alteredFunctions = [];

    /** @var array All altered routine's diff */
    public $procedureDiffs = [], $functionDiffs = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    protected function interruptIsEmptyByRoutine()
    {
        if (
            count($this->createdProcedures) || count($this->droppedProcedures) || count($this->alteredProcedures) ||
            count($this->createdFunctions) || count($this->droppedFunctions) || count($this->alteredFunctions)
        ) {
            return false;
        }
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::_toSql()
     */
    protected function intercept_ToSqlByRoutine($sql, AbstractPlatform $platform, $saveMode)
    {
        foreach ($this->createdProcedures as $routine) {
            $sql[] = $platform->getCreateProcedureSQL($routine);
        }

        foreach ($this->createdFunctions as $routine) {
            $sql[] = $platform->getCreateFunctionSQL($routine);
        }

        if (! $saveMode) {
            foreach ($this->droppedProcedures as $routine) {
                $sql[] = $platform->getDropProcedureSQL($routine->getQuotedName($platform), $routine->getOption('parameters'));
            }

            foreach ($this->droppedFunctions as $routine) {
                $sql[] = $platform->getDropFunctionSQL($routine->getQuotedName($platform), $routine->getOption('parameters'));
            }
        }

        foreach ($this->alteredProcedures as $n => $routine) {
            $alteredSqls       = $platform->getAlterProcedureSQL($routine);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $platform->buildDiffComment($this->procedureDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        foreach ($this->alteredFunctions as $n => $routine) {
            $alteredSqls       = $platform->getAlterFunctionSQL($routine);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $platform->buildDiffComment($this->functionDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        return ['sql' => $sql];
    }
}
