<?php

namespace Doctrine\DBAL\Plugin\Event\Platforms;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Event;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    public function supportsEvents(): bool
    {
        return false;
    }

    public function getCreateEventSQL(Event $event): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getDropEventSQL(string $event): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getAlterEventSQL(Event $event): array
    {
        return [
            $this->getDropEventSQL($event->getQuotedName($this)),
            $this->getCreateEventSQL($event),
        ];
    }
}
