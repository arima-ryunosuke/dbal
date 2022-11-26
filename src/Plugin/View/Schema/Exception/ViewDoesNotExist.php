<?php

namespace Doctrine\DBAL\Plugin\View\Schema\Exception;

use Doctrine\DBAL\Schema\SchemaException;
use LogicException;

final class ViewDoesNotExist extends LogicException implements SchemaException
{
    public static function new(string $viewName): self
    {
        return new self("There is no view with name '" . $viewName . "' in the schema.");
    }
}
