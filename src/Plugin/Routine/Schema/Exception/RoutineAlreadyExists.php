<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use LogicException;

final class RoutineAlreadyExists extends LogicException implements SchemaException
{
    public static function new(string $routineName): self
    {
        return new self("The routine with name '" . $routineName . "' already exists.");
    }
}
