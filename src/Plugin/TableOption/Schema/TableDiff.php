<?php

namespace Doctrine\DBAL\Plugin\TableOption\Schema;

trait TableDiff
{
    /** @var array All changed table option */
    public $changedOptions = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\TableDiff::isEmpty()
     */
    protected function interruptIsEmptyByTableOption()
    {
        if (count($this->changedOptions)) {
            return false;
        }
    }
}
