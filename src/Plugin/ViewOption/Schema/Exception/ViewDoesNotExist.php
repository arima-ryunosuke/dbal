<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;

final class ViewDoesNotExist extends SchemaException
{
    public static function new(string $viewName): self
    {
        return new self("There is no view with name '" . $viewName . "' in the schema.");
    }
}
