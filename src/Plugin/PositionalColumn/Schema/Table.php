<?php

namespace Doctrine\DBAL\Plugin\PositionalColumn\Schema;

use Doctrine\DBAL\Schema\SchemaConfig;

trait Table
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Table::getColumns()
     */
    protected function interruptGetColumnsByPositionalColumn()
    {
        if ($this->_schemaConfig instanceof SchemaConfig && $this->_schemaConfig->getPositionalColumn()) {
            return $this->_columns;
        }
    }
}
