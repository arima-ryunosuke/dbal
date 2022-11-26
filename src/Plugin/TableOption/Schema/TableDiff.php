<?php

namespace Doctrine\DBAL\Plugin\TableOption\Schema;

use Doctrine\DBAL\Plugin\Interrupt;

trait TableDiff
{
    public array $changedOptions = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\TableDiff::isEmpty()
     */
    #[Interrupt]
    protected function isEmptyByTableOption(): ?bool
    {
        if (count($this->changedOptions)) {
            return false;
        }

        return null;
    }
}
