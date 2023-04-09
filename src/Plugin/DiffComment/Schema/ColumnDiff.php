<?php

namespace Doctrine\DBAL\Plugin\DiffComment\Schema;

trait ColumnDiff
{
    public function getDiff(): array
    {
        if (! $this->getOldColumn()) {
            return [[], []];
        }

        $changedProperties = array_flip($this->changedProperties);

        return [
            array_intersect_key($this->getOldColumn()->toArray(), $changedProperties),
            array_intersect_key($this->getNewColumn()->toArray(), $changedProperties),
        ];
    }
}
