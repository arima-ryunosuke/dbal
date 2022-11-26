<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

use Doctrine\DBAL\Schema\View;

trait SchemaDiff
{
    /** @var View[] */
    public array $createdViews = [];

    /** @var View[] */
    public array $droppedViews = [];

    /** @var View[] */
    public array $alteredViews = [];

    public array $viewDiffs = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    protected function interruptIsEmptyByView(): ?bool
    {
        if (count($this->createdViews) || count($this->alteredViews) || count($this->droppedViews)) {
            return false;
        }

        return null;
    }
}
