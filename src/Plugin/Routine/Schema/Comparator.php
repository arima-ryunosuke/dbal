<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::compareSchemas()
     */
    protected function interceptCompareSchemasByRoutine(Schema $oldSchema, Schema $newSchema, SchemaDiff $diff): array
    {
        foreach ($oldSchema->getRoutines() as $routine) {
            if ($newSchema->hasRoutine($routine->getLocalName())) {
                continue;
            }

            /** @see SchemaDiff::$droppedProcedures, SchemaDiff::$droppedFunctions */
            $type = ucfirst(strtolower($routine->getType()));
            $key  = "dropped{$type}s";

            assert(is_array($diff->$key));
            $diff->$key[] = $routine;
        }

        foreach ($newSchema->getRoutines() as $routine) {
            if (! $oldSchema->hasRoutine($routine->getLocalName())) {
                /** @see SchemaDiff::$createdProcedures, SchemaDiff::$createdFunctions */
                $type = ucfirst(strtolower($routine->getType()));
                $key  = "created{$type}s";

                assert(is_array($diff->$key));
                $diff->$key[] = $routine;
            } elseif ($diffs = $this->diffRoutine($oldRoutine = $oldSchema->getRoutine($routine->getLocalName()), $routine)) {
                /** @see SchemaDiff::$alteredProcedures, SchemaDiff::$alteredFunctions */
                $type     = ucfirst(strtolower($routine->getType()));
                $key      = "altered{$type}s";
                $keyDiffs = lcfirst("{$type}Diffs");

                assert(is_array($diff->$key));
                $routine->addOption('old', $oldRoutine);
                $diff->$key[]      = $routine;
                $diff->$keyDiffs[] = $diffs;
            }
        }

        return [];
    }

    public function diffRoutine(Routine $routine1, Routine $routine2): array
    {
        $diff = [];

        if (trim($routine1->getStatement()) !== trim($routine2->getStatement())) {
            $diff[0]['statement'] = $routine1->getStatement();
            $diff[1]['statement'] = $routine2->getStatement();
        }

        if ($routine1->getOptions() != $routine2->getOptions()) {
            $diff[0]['options'] = $routine1->getOptions();
            $diff[1]['options'] = $routine2->getOptions();
        }

        return $diff;
    }
}
