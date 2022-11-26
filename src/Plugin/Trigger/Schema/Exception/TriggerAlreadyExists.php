<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;

final class TriggerAlreadyExists extends SchemaException
{
    public static function new(string $triggerName): self
    {
        return new self("The trigger with name '" . $triggerName . "' already exists.");
    }
}
