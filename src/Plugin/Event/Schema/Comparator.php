<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Schema\Event;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::CompareSchemas()
     */
    protected function interceptCompareSchemasByEvent(Schema $fromSchema, Schema $toSchema, SchemaDiff $diff)
    {
        foreach ($fromSchema->getEvents() as $event) {
            if ($toSchema->hasEvent($event->getName())) {
                continue;
            }

            $diff->droppedEvents[] = $event;
        }

        foreach ($toSchema->getEvents() as $event) {
            if (! $fromSchema->hasEvent($event->getName())) {
                $diff->createdEvents[] = $event;
            } elseif ($this->diffEvent($event, $fromSchema->getEvent($event->getName()))) {
                $diff->alteredEvents[] = $event;
            }
        }
    }

    public function diffEvent(Event $event1, Event $event2): bool
    {
        if (trim($event1->getStatement()) !== trim($event2->getStatement())) {
            return true;
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
            return true;
        }

        return false;
    }
}
