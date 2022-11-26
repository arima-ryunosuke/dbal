<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Trigger;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::compareSchemas()
     */
    protected function interceptCompareSchemasByTrigger(Schema $oldSchema, Schema $newSchema, SchemaDiff $diff): array
    {
        foreach ($oldSchema->getTriggers() as $trigger) {
            if ($newSchema->hasTrigger($trigger->getName())) {
                continue;
            }

            $diff->droppedTriggers[] = $trigger;
        }

        foreach ($newSchema->getTriggers() as $trigger) {
            if (! $oldSchema->hasTrigger($trigger->getName())) {
                $diff->createdTriggers[] = $trigger;
            } elseif ($diffs = $this->diffTrigger($oldSchema->getTrigger($trigger->getName()), $trigger)) {
                $diff->alteredTriggers[] = $trigger;
                $diff->triggerDiffs[]    = $diffs;
            }
        }

        return [];
    }

    public function diffTrigger(Trigger $trigger1, Trigger $trigger2): array
    {
        $diff = [];

        if (trim($trigger1->getStatement()) !== trim($trigger2->getStatement())) {
            $diff[0]['statement'] = $trigger1->getStatement();
            $diff[1]['statement'] = $trigger2->getStatement();
        }

        if ($trigger1->getTableName() !== $trigger2->getTableName()) {
            $diff[0]['table'] = $trigger1->getTableName();
            $diff[1]['table'] = $trigger2->getTableName();
        }

        if ($trigger1->getOptions() != $trigger2->getOptions()) {
            $diff[0]['options'] = $trigger1->getOptions();
            $diff[1]['options'] = $trigger2->getOptions();
        }

        return $diff;
    }
}
