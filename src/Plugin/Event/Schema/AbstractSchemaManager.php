<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Event;
use Doctrine\DBAL\Schema\Schema;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::introspectSchema()
     */
    protected function interceptIntrospectSchemaByEvent(Schema $schema): array
    {
        if ($this->connection->getDatabasePlatform()->supportsEvents()) {
            foreach ($this->listEvents() as $event) {
                $schema->addEvent($event);
            }
        }

        return [];
    }

    /** @return Event[] */
    public function listEvents(): array
    {
        $database = $this->getDatabase(__METHOD__);

        return $this->_getPortableEventList(
            $this->selectEvents($database)->fetchAllAssociative()
        );
    }

    /** @return Event[] */
    protected function _getPortableEventList(array $events): array
    {
        $list = array_filter(array_map(fn($value) => $this->_getPortableEventDefinition($value), $events));
        $keys = array_map(fn($value) => strtolower($value->getQuotedName($this->platform)), $list);

        return array_combine($keys, $list);
    }

    protected function _getPortableEventDefinition(array $event): Event
    {
        assert(isset($event['name'], $event['statement']));
        $name = $event['name'];
        $stmt = $event['statement'];
        unset($event['name'], $event['statement']);

        return new Event($name, $stmt, $event);
    }

    protected function selectEvents(string $databaseName): Result
    {
        throw NotSupported::new(__METHOD__); // @codeCoverageIgnore
    }

    public function createEvent(Event $event): void
    {
        $this->connection->executeStatement(
            $this->platform->getCreateEventSQL($event),
        );
    }

    public function dropEvent(string $name): void
    {
        $this->connection->executeStatement(
            $this->platform->getDropEventSQL($name),
        );
    }
}
