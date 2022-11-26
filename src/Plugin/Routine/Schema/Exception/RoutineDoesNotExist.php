<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use LogicException;

final class RoutineDoesNotExist extends LogicException implements SchemaException
{
    public static function new(string $routineName): self
    {
        return new self("There is no routine with name '" . $routineName . "' in the schema.");
    }
}
