<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Trigger;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::CompareSchemas()
     */
    protected function interceptCompareSchemasByTrigger(Schema $fromSchema, Schema $toSchema, SchemaDiff $diff)
    {
        foreach ($fromSchema->getTriggers() as $trigger) {
            if ($toSchema->hasTrigger($trigger->getName())) {
                continue;
            }

            $diff->droppedTriggers[] = $trigger;
        }

        foreach ($toSchema->getTriggers() as $trigger) {
            if (! $fromSchema->hasTrigger($trigger->getName())) {
                $diff->createdTriggers[] = $trigger;
            } elseif ($this->diffTrigger($trigger, $fromSchema->getTrigger($trigger->getName()))) {
                $diff->alteredTriggers[] = $trigger;
            }
        }
    }

    public function diffTrigger(Trigger $trigger1, Trigger $trigger2): bool
    {
        if (trim($trigger1->getStatement()) !== trim($trigger2->getStatement())) {
            return true;
        }

        if ($trigger1->getTableName() !== $trigger2->getTableName()) {
            return true;
        }

        if ($trigger1->getOptions() != $trigger2->getOptions()) {
            return true;
        }

        return false;
    }
}
