<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

use Doctrine\DBAL\Plugin\View\Schema\Exception\ViewAlreadyExists;
use Doctrine\DBAL\Plugin\View\Schema\Exception\ViewDoesNotExist;
use Doctrine\DBAL\Schema\View;

trait Schema
{
    /** @var View[] */
    protected array $_views = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\Schema::__clone()
     */
    protected function intercept__CloneByView(): array
    {
        foreach ($this->_views as $k => $view) {
            $this->_views[$k] = clone $view;
        }

        return [];
    }

    /** @return View[] */
    public function getViews(): array
    {
        return $this->_views;
    }

    public function getView(string $viewName): View
    {
        $name = $this->getKeyFromName($viewName);
        if (! isset($this->_views[$name])) {
            throw ViewDoesNotExist::new($name);
        }

        return $this->_views[$name];
    }

    public function hasView(string $viewName): bool
    {
        $name = $this->getKeyFromName($viewName);

        return isset($this->_views[$name]);
    }

    public function addView(View $view): self
    {
        $viewName = $this->getKeyFromResolvedName($view);

        if (isset($this->_views[$viewName])) {
            throw ViewAlreadyExists::new($viewName);
        }

        $this->_views[$viewName] = $view;

        $this->_createNamespaceBy($view);

        return $this;
    }

    public function dropView(string $viewName): self
    {
        $name = $this->getKeyFromName($viewName);
        if (! isset($this->_views[$name])) {
            throw ViewDoesNotExist::new($name);
        }

        unset($this->_views[$name]);

        return $this;
    }
}
