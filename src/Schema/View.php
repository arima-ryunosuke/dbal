<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Schema;

use Doctrine\DBAL\Schema\Name\OptionallyQualifiedName;
use Doctrine\DBAL\Schema\Name\Parser\OptionallyQualifiedNameParser;
use Doctrine\DBAL\Schema\Name\Parsers;

/**
 * Representation of a Database View.
 *
 * @extends AbstractNamedObject<OptionallyQualifiedName>
 */
class View extends AbstractNamedObject
{
    use \Doctrine\DBAL\Plugin\All\Schema\View;

    public function __construct(string $name, private readonly string $sql, $options = [])
    {
        parent::__construct($name);

        extract($this->intercept(get_defined_vars()));
    }

    protected function getNameParser(): OptionallyQualifiedNameParser
    {
        return Parsers::getOptionallyQualifiedNameParser();
    }

    public function getSql(): string
    {
        return $this->sql;
    }
}
