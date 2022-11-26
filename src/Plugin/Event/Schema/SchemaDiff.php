<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Plugin\Interrupt;
use Doctrine\DBAL\Schema\Event;

trait SchemaDiff
{
    /** @var Event[] */
    public array $createdEvents = [];

    /** @var Event[] */
    public array $droppedEvents = [];

    /** @var Event[] */
    public array $alteredEvents = [];

    public array $eventDiffs = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    #[Interrupt]
    protected function isEmptyByEventByEvent(): ?bool
    {
        if (count($this->createdEvents) || count($this->droppedEvents) || count($this->alteredEvents)) {
            return false;
        }

        return null;
    }
}
