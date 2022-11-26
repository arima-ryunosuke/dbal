<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Schema;

use Doctrine\DBAL\Plugin\ViewOption\Schema\Exception\ViewDoesNotExist;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::createSchema()
     */
    protected function interceptIntrospectSchemaByViewOption(Schema $schema)
    {
        foreach ($this->listViews() as $view) {
            $schema->addView($view);
        }
    }

    /**
     * Introspects the view as Table with the given name.
     */
    public function introspectViewAsTable(string $name): Table
    {
        $views = $this->listViews();

        if (! isset($views[$name])) {
            throw ViewDoesNotExist::new($name);
        }

        $view = $views[$name];

        $database = $this->_conn->getDatabase();

        $sql          = $this->_platform->getListTableColumnsSQL($name, $database);
        $tableColumns = $this->_conn->fetchAllAssociative($sql);
        $columns      = $this->_getPortableTableColumnList($name, $database, $tableColumns);

        $sql          = $this->_platform->getListTableIndexesSQL($name, $database);
        $tableIndexes = $this->_conn->fetchAllAssociative($sql);
        $indexes      = $this->_getPortableTableIndexesList($tableIndexes, $name);

        return new Table($name, $columns, $indexes, [], [], [
            'view_options' => $view->getOptions(),
        ]);
    }
}
