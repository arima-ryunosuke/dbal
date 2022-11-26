<?php

namespace Doctrine\DBAL\Plugin\View\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use LogicException;

final class ViewAlreadyExists extends LogicException implements SchemaException
{
    public static function new(string $viewName): self
    {
        return new self("The view with name '" . $viewName . "' already exists.");
    }
}
