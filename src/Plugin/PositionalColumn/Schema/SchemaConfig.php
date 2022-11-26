<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Schema;

trait SchemaConfig
{
    /** @var bool */
    protected $positionalColumn;

    public function getPositionalColumn()
    {
        return $this->positionalColumn;
    }

    public function setPositionalColumn(bool $positional)
    {
        $this->positionalColumn = $positional;
    }
}
