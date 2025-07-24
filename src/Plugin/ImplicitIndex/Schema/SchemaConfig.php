<?php

namespace Doctrine\DBAL\Plugin\ImplicitIndex\Schema;

trait SchemaConfig
{
    protected bool $explicitForeignKeyIndexes = false;

    public function hasExplicitForeignKeyIndexes(): bool
    {
        return $this->explicitForeignKeyIndexes;
    }

    public function setExplicitForeignKeyIndexes(bool $positional): void
    {
        $this->explicitForeignKeyIndexes = $positional;
    }
}
