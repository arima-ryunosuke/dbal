<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;

final class ViewAlreadyExists extends SchemaException
{
    public static function new(string $viewName): self
    {
        return new self("The view with name '" . $viewName . "' already exists.");
    }
}
