<?php

namespace Doctrine\DBAL\Plugin\Event\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use LogicException;

final class EventAlreadyExists extends LogicException implements SchemaException
{
    public static function new(string $eventName): self
    {
        return new self("The event with name '" . $eventName . "' already exists.");
    }
}
