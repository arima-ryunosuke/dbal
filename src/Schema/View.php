<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Schema;

/**
 * Representation of a Database View.
 */
class View extends AbstractAsset
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\All\Schema\View;

    public function __construct(string $name, private readonly string $sql, $options = [])
    {
        $this->_setName($name);
        extract($this->intercept(get_defined_vars()));
    }

    public function getSql(): string
    {
        return $this->sql;
    }
}
