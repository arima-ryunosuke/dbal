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
            } elseif ($this->diffView($view, $fromSchema->getView($view->getName()))) {
                $diff->alteredViews[] = $view;
            }
        }
    }

    public function diffView(View $view1, View $view2): bool
    {
        if (trim($view1->getSql()) !== trim($view2->getSql())) {
            return true;
        }

        $option1 = $view1->getOptions();
        $option2 = $view2->getOptions();

        // updatable is readonly
        unset($option1['updatable']);
        unset($option2['updatable']);

        if ($option1 != $option2) {
            return true;
        }

        return false;
    }
}
