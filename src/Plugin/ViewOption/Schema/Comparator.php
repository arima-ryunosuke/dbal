<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\View;

trait Comparator
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Comparator::CompareSchemas()
     */
    protected function interceptCompareSchemasByViewOption(Schema $fromSchema, Schema $toSchema, SchemaDiff $diff)
    {
        foreach ($fromSchema->getViews() as $view) {
            if ($toSchema->hasView($view->getName())) {
                continue;
            }

            $diff->droppedViews[] = $view;
        }

        foreach ($toSchema->getViews() as $view) {
            if (! $fromSchema->hasView($view->getName())) {
                $diff->createdViews[] = $view;
            } elseif ($diffs = $this->diffView($fromSchema->getView($view->getName()), $view)) {
                $diff->alteredViews[] = $view;
                $diff->viewDiffs[]    = $diffs;
            }
        }
    }

    public function diffView(View $view1, View $view2): array
    {
        $diff = [];

        if (trim($view1->getSql()) !== trim($view2->getSql())) {
            $tokens1 = preg_split('#\s+|,#u', $view1->getSql(), -1, PREG_SPLIT_NO_EMPTY);
            $tokens2 = preg_split('#\s+|,#u', $view2->getSql(), -1, PREG_SPLIT_NO_EMPTY);

            $diff1 = array_diff($tokens1, $tokens2);
            $diff2 = array_diff($tokens2, $tokens1);

            $diff[0]['sql'] = implode(",", $diff1);
            $diff[1]['sql'] = implode(",", $diff2);
        }

        $options1 = $view1->getOptions();
        $options2 = $view2->getOptions();

        // updatable is readonly
        unset($options1['updatable']);
        unset($options2['updatable']);

        if ($options1 != $options2) {
            $diff[0]['options'] = $options1;
            $diff[1]['options'] = $options2;
        }

        return $diff;
    }
}
