<?php

namespace Doctrine\DBAL\Plugin\TableOption\Schema;

trait TableDiff
{
    public array $changedOptions = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\TableDiff::isEmpty()
     */
    protected function interruptIsEmptyByTableOption(): ?bool
    {
        if (count($this->changedOptions)) {
            return false;
        }

        return null;
    }
}
