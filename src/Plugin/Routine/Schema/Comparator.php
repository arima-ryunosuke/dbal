<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::CompareSchemas()
     */
    protected function interceptCompareSchemasByRoutine(Schema $fromSchema, Schema $toSchema, SchemaDiff $diff)
    {
        foreach ($fromSchema->getRoutines() as $routine) {
            if ($toSchema->hasRoutine($routine->getName())) {
                continue;
            }

            /** @see SchemaDiff::$droppedProcedures, SchemaDiff::$droppedFunctions */
            $type = ucfirst(strtolower($routine->getType()));
            $key  = "dropped{$type}s";

            assert(is_array($diff->$key));
            $diff->$key[] = $routine;
        }

        foreach ($toSchema->getRoutines() as $routine) {
            if (! $fromSchema->hasRoutine($routine->getName())) {
                /** @see SchemaDiff::$createdProcedures, SchemaDiff::$createdFunctions */
                $type = ucfirst(strtolower($routine->getType()));
                $key  = "created{$type}s";

                assert(is_array($diff->$key));
                $diff->$key[] = $routine;
            } elseif ($this->diffRoutine($routine, $oldRoutine = $fromSchema->getRoutine($routine->getName()))) {
                /** @see SchemaDiff::$alteredProcedures, SchemaDiff::$alteredFunctions */
                $type = ucfirst(strtolower($routine->getType()));
                $key  = "altered{$type}s";

                assert(is_array($diff->$key));
                $routine->addOption('old', $oldRoutine);
                $diff->$key[] = $routine;
            }
        }
    }

    public function diffRoutine(Routine $routine1, Routine $routine2): bool
    {
        if (trim($routine1->getStatement()) !== trim($routine2->getStatement())) {
            return true;
        }

        if ($routine1->getOptions() != $routine2->getOptions()) {
            return true;
        }

        return false;
    }
}
