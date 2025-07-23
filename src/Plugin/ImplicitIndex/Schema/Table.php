<?php

namespace Doctrine\DBAL\Plugin\ImplicitIndex\Schema;

use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\DBAL\Schema\SchemaException;

trait Table
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Table::getIndex()
     */
    protected function interruptGetIndexByImplicitIndex(string $name)
    {
        if ($this->_schemaConfig instanceof SchemaConfig && $this->_schemaConfig->hasExplicitForeignKeyIndexes()) {
            if (isset($this->implicitIndexes[$name])) {
                throw SchemaException::indexDoesNotExist($name, $this->_name);
            }
        }
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\Table::getIndexes()
     */
    protected function interruptGetIndexesByImplicitIndex()
    {
        if ($this->_schemaConfig instanceof SchemaConfig && $this->_schemaConfig->hasExplicitForeignKeyIndexes()) {
            return array_diff_key($this->_indexes, $this->implicitIndexes);
        }
    }
}
