<?php

namespace Doctrine\DBAL\Plugin\Event\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;

final class EventDoesNotExist extends SchemaException
{
    public static function new(string $eventName): self
    {
        return new self("There is no event with name '" . $eventName . "' in the schema.");
    }
}
