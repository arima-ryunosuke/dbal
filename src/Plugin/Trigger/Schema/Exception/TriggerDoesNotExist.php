<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use LogicException;

final class TriggerDoesNotExist extends LogicException implements SchemaException
{
    public static function new(string $triggerName): self
    {
        return new self("There is no trigger with name '" . $triggerName . "' in the schema.");
    }
}
