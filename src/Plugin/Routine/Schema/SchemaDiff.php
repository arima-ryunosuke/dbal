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

        foreach ($this->alteredProcedures as $routine) {
            $sql = array_merge($sql, $platform->getAlterProcedureSQL($routine));
        }

        foreach ($this->alteredFunctions as $routine) {
            $sql = array_merge($sql, $platform->getAlterFunctionSQL($routine));
        }

        return ['sql' => $sql];
    }
}
