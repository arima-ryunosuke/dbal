<?php

namespace Doctrine\DBAL\Plugin\Event\Platforms;

use Doctrine\DBAL\Schema\Event;

trait MySQLPlatform
{
    public function supportsEvents(): bool
    {
        return true;
    }

    public function getCreateEventSQL(Event $event): string
    {
        return 'CREATE ' . $this->_getEventSQL($event);
    }

    public function getDropEventSQL(string $event): string
    {
        return "DROP EVENT $event";
    }

    public function getAlterEventSQL(Event $event): array
    {
        return ['ALTER ' . $this->_getEventSQL($event)];
    }

    private function _getEventSQL(Event $event): string
    {
        $eventName = $event->getLocalName();
        $statement = $event->getStatement();
        $options   = $event->getOptions();
        $definer   = $this->_quoteUserHostname($options['definer'] ?? '');

        return implode("\n", array_filter([
            ($definer ? "DEFINER=$definer " : "") . "EVENT $eventName",
            "ON SCHEDULE EVERY {$this->quoteStringLiteral($options['intervalValue'])} {$options['intervalField']}",
            $options['since'] ? "STARTS {$this->quoteStringLiteral($options['since'])}" : '',
            $options['until'] ? "ENDS {$this->quoteStringLiteral($options['until'])}" : '',
            "ON COMPLETION {$options['completion']}",
            "{$options['status']}",
            "COMMENT {$this->quoteStringLiteral($options['comment'])}",
            "DO $statement",
        ], 'strlen'));
    }
}
