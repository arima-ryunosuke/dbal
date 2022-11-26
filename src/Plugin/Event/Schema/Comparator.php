<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Schema\Event;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::compareSchemas()
     */
    protected function interceptCompareSchemasByEvent(Schema $oldSchema, Schema $newSchema, SchemaDiff $diff): array
    {
        foreach ($oldSchema->getEvents() as $event) {
            if ($newSchema->hasEvent($event->getName())) {
                continue;
            }

            $diff->droppedEvents[] = $event;
        }

        foreach ($newSchema->getEvents() as $event) {
            if (! $oldSchema->hasEvent($event->getName())) {
                $diff->createdEvents[] = $event;
            } elseif ($diffs = $this->diffEvent($oldSchema->getEvent($event->getName()), $event)) {
                $diff->alteredEvents[] = $event;
                $diff->eventDiffs[]    = $diffs;
            }
        }

        return [];
    }

    public function diffEvent(Event $event1, Event $event2): array
    {
        $diff = [];

        if (trim($event1->getStatement()) !== trim($event2->getStatement())) {
            $diff[0]['statement'] = $event1->getStatement();
            $diff[1]['statement'] = $event2->getStatement();
        }

        $options1 = $event1->getOptions();
        $options2 = $event2->getOptions();

        // timezone is session value and not present in create/alter SQL
        unset($options1['timeZone']);
        unset($options2['timeZone']);

        // interval is generated value by intervalField/intervalValue
        unset($options1['interval']);
        unset($options2['interval']);

        if ($options1 != $options2) {
            $diff[0]['options'] = $options1;
            $diff[1]['options'] = $options2;
        }

        return $diff;
    }
}
