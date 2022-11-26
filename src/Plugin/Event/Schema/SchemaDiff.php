<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Event;

trait SchemaDiff
{
    /**
     * All created event
     *
     * @var Event[]
     */
    public $createdEvents = [];

    /**
     * All dropped event
     *
     * @var Event[]|string[]
     */
    public $droppedEvents = [];

    /**
     * All altered event
     *
     * @var Event[]
     */
    public $alteredEvents = [];

    /**
     * All altered event's diff
     *
     * @var array
     */
    public $eventDiffs = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    protected function interruptIsEmptyByEvent()
    {
        if (count($this->createdEvents) || count($this->droppedEvents) || count($this->alteredEvents)) {
            return false;
        }
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::_toSql()
     */
    protected function intercept_ToSqlByEvent($sql, AbstractPlatform $platform, $saveMode)
    {
        foreach ($this->createdEvents as $event) {
            $sql[] = $platform->getCreateEventSQL($event);
        }

        if (! $saveMode) {
            foreach ($this->droppedEvents as $event) {
                $sql[] = $platform->getDropEventSQL($event->getQuotedName($platform));
            }
        }

        foreach ($this->alteredEvents as $n => $event) {
            $alteredSqls       = $platform->getAlterEventSQL($event);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $platform->buildDiffComment($this->eventDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        return ['sql' => $sql];
    }
}
