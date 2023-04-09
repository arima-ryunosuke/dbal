<?php

namespace Doctrine\DBAL\Plugin\DiffComment\Schema;

trait TableDiff
{
    public function getDiff(): array
    {
        $changedOptions = $this->changedOptions;
        unset($changedOptions['create_options']);

        return [
            array_intersect_key($this->getOldTable()->getOptions(), $changedOptions),
            $changedOptions,
        ];
    }
}
