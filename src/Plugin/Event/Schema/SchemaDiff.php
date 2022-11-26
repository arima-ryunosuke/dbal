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

        foreach ($this->alteredEvents as $event) {
            $sql = array_merge($sql, $platform->getAlterEventSQL($event));
        }

        return ['sql' => $sql];
    }
}
