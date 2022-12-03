<?php

namespace Doctrine\DBAL\Plugin\IndexOption\Schema;

use Doctrine\DBAL\Schema\Index;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQL\Comparator::diffIndex()
     */
    protected function interruptDiffIndexByIndexOption(Index $index1, Index $index2): ?bool
    {
        $flags1 = array_fill_keys($index1->getFlags(), true);
        $flags2 = array_fill_keys($index2->getFlags(), true);

        // ignore implicit index
        if (isset($flags1['implicit']) || isset($flags2['implicit'])) {
            return false;
        }

        // normalize clustered/nonclustered flag when create as offline
        if ($index1->isPrimary() && (! isset($flags1['clustered']) || isset($flags1['nonclustered']))) {
            $flags1['clustered'] = true;
            unset($flags1['nonclustered']);
        }
        if ($index2->isPrimary() && (! isset($flags2['clustered']) || isset($flags2['nonclustered']))) {
            $flags2['clustered'] = true;
            unset($flags2['nonclustered']);
        }

        if ($flags1 != $flags2) {
            return true;
        }

        $options1 = $index1->getOptions();
        $options2 = $index2->getOptions();

        // lengths is covered by isFulfilledBy
        unset($options1['lengths']);
        unset($options2['lengths']);

        if ($options1 != $options2) {
            return true;
        }

        return null;
    }
}
