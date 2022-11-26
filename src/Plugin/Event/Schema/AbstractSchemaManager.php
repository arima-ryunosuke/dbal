<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Event;
use Doctrine\DBAL\Schema\Schema;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::createSchema()
     */
    protected function interceptIntrospectSchemaByEvent(Schema $schema)
    {
        if ($this->_conn->getDatabasePlatform()->supportsEvents()) {
            foreach ($this->listEvents() as $event) {
                $schema->addEvent($event);
            }
        }
    }

    public function listEvents()
    {
        $database = $this->getDatabase(__METHOD__);

        return $this->_getPortableEventList(
            $this->selectEvents($database)->fetchAllAssociative()
        );
    }

    protected function _getPortableEventList($events)
    {
        $list = array_filter(array_map(fn($value) => $this->_getPortableEventDefinition($value), $events));
        $keys = array_map(fn($value) => strtolower($value->getQuotedName($this->_platform)), $list);

        return array_combine($keys, $list);
    }

    protected function _getPortableEventDefinition($event)
    {
        assert(isset($event['name'], $event['statement']));
        $name = $event['name'];
        $stmt = $event['statement'];
        unset($event['name'], $event['statement']);

        return new Event($name, $stmt, $event);
    }

    protected function selectEvents(string $databaseName): Result
    {
        throw Exception::notSupported(__METHOD__); // @codeCoverageIgnore
    }

    public function createEvent(Event $event)
    {
        $this->_conn->executeStatement(
            $this->_platform->getCreateEventSQL($event),
        );
    }

    public function dropEvent(string $name)
    {
        $this->_conn->executeStatement(
            $this->_platform->getDropEventSQL($name),
        );
    }
}
