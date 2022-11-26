<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Plugin\View\Schema\Exception\ViewDoesNotExist;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::introspectSchema()
     */
    protected function interceptIntrospectSchemaByView(Schema $schema): array
    {
        foreach ($this->listViews() as $view) {
            $schema->addView($view);
        }

        return [];
    }

    /**
     * Introspects the view as Table with the given name.
     */
    public function introspectViewAsTable(string $name): Table
    {
        $views = [];
        foreach ($this->listViews() as $view) {
            $views[$view->getShortestName($view->getNamespaceName())] = $view;
        }

        if (! isset($views[$name])) {
            throw ViewDoesNotExist::new($name);
        }

        $view = $views[$name];

        $database = $this->getDatabase(__METHOD__);

        return new Table($name,
            $this->_getPortableTableColumnList($name, $database, $this->selectViewColumns($database, $name)->fetchAllAssociative()),
            $this->_getPortableTableIndexesList($this->selectViewIndexes($database, $name)->fetchAllAssociative(), $name),
            options: [
                'view_options' => $view->getOptions(),
            ]);
    }

    protected function selectViewColumns(string $databaseName, string $viewName): Result
    {
        throw NotSupported::new(__METHOD__); // @codeCoverageIgnore
    }

    protected function selectViewIndexes(string $databaseName, string $viewName): Result
    {
        // Most RDBMS do not support index of views
        return $this->connection->executeQuery('SELECT 1 WHERE 1=0');
    }
}
