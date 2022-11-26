<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Schema;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\View;

trait SchemaDiff
{
    /** @var View[] All created views */
    public $createdViews = [];

    /** @var View[] All dropped views */
    public $droppedViews = [];

    /** @var View[] All altered views */
    public $alteredViews = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    protected function interruptIsEmptyByViewOption()
    {
        if (count($this->createdViews) || count($this->alteredViews) || count($this->droppedViews)) {
            return false;
        }
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::_toSql()
     */
    protected function intercept_ToSqlByViewOption($sql, AbstractPlatform $platform, $saveMode)
    {
        foreach ($this->createdViews as $view) {
            $sql[] = $platform->getCreateViewSQL($view->getName(), $view->getSql(), $view->getOptions());
        }

        if (! $saveMode) {
            foreach ($this->droppedViews as $view) {
                $sql[] = $platform->getDropViewSQL($view->getName());
            }
        }

        foreach ($this->alteredViews as $view) {
            $sql = array_merge($sql, $platform->getReplaceViewSQL($view->getName(), $view->getSql(), $view->getOptions()));
        }

        return ['sql' => $sql];
    }
}
