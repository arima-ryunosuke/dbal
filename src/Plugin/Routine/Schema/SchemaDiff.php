<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Schema\Routine;

trait SchemaDiff
{
    /** @var Routine[] */
    public array $createdProcedures = [];
    /** @var Routine[] */
    public array $createdFunctions = [];

    /** @var Routine[] */
    public array $droppedProcedures = [];
    /** @var Routine[] */
    public array $droppedFunctions = [];

    /** @var Routine[] */
    public array $alteredProcedures = [];
    /** @var Routine[] */
    public array $alteredFunctions = [];

    public array $procedureDiffs = [];
    public array $functionDiffs  = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    protected function interruptIsEmptyByRoutine(): ?bool
    {
        if (
            count($this->createdProcedures) || count($this->droppedProcedures) || count($this->alteredProcedures) ||
            count($this->createdFunctions) || count($this->droppedFunctions) || count($this->alteredFunctions)
        ) {
            return false;
        }

        return null;
    }
}
