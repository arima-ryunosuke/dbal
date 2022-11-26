<?php

namespace Doctrine\DBAL\Plugin\Event\Platforms;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Schema\Event;
use Doctrine\DBAL\Schema\SchemaDiff;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterSchemaSQL()
     */
    protected function interceptGetAlterSchemaSQLByEvent(array $sql, SchemaDiff $diff): array
    {
        foreach ($diff->createdEvents as $event) {
            $sql[] = $this->getCreateEventSQL($event);
        }

        foreach ($diff->droppedEvents as $event) {
            $sql[] = $this->getDropEventSQL($event->getQuotedName($this));
        }

        foreach ($diff->alteredEvents as $n => $event) {
            $alteredSqls       = $this->getAlterEventSQL($event);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $this->buildDiffComment($diff->eventDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        return ['sql' => $sql];
    }

    public function supportsEvents(): bool
    {
        return false;
    }

    public function getCreateEventSQL(Event $event): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getDropEventSQL(string $event): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getAlterEventSQL(Event $event): array
    {
        return [
            $this->getDropEventSQL($event->getQuotedName($this)),
            $this->getCreateEventSQL($event),
        ];
    }
}
