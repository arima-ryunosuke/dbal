<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Plugin\Event\Schema\Exception\EventAlreadyExists;
use Doctrine\DBAL\Plugin\Event\Schema\Exception\EventDoesNotExist;
use Doctrine\DBAL\Schema\Event;

trait Schema
{
    /** @var Event[] */
    protected array $_events = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\Schema::__clone()
     */
    protected function intercept__CloneByEvent(): array
    {
        foreach ($this->_events as $k => $event) {
            $this->_events[$k] = clone $event;
        }

        return [];
    }

    /** @return Event[] */
    public function getEvents(): array
    {
        return $this->_events;
    }

    public function getEvent(string $eventName): Event
    {
        $name = $this->getFullQualifiedAssetName($eventName);
        if (! isset($this->_events[$name])) {
            throw EventDoesNotExist::new($name);
        }

        return $this->_events[$name];
    }

    public function hasEvent(string $eventName): bool
    {
        $name = $this->getFullQualifiedAssetName($eventName);

        return isset($this->_events[$name]);
    }

    public function addEvent(Event $event): self
    {
        $eventName = $this->normalizeName($event);

        if (isset($this->_events[$eventName])) {
            throw EventAlreadyExists::new($eventName);
        }

        $this->_events[$eventName] = $event;

        $this->_createNamespaceBy($event);

        return $this;
    }

    public function dropEvent(string $eventName): self
    {
        $name = $this->getFullQualifiedAssetName($eventName);
        $this->getEvent($name);
        unset($this->_events[$name]);

        return $this;
    }
}
