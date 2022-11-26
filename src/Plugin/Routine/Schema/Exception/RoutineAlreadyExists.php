<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;

final class RoutineAlreadyExists extends SchemaException
{
    public static function new(string $routineName): self
    {
        return new self("The routine with name '" . $routineName . "' already exists.");
    }
}
