<?php

namespace Doctrine\DBAL\Plugin\ForeignKeyConstraintOption\Schema;

trait ForeignKeyConstraint
{
    public function addOption(string $name, mixed $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }
}
