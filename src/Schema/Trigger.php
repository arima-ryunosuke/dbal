<?php

namespace Doctrine\DBAL\Schema;

/**
 * Representation of a Database Trigger.
 */
class Trigger extends AbstractAsset
{
    /** @var string */
    private $_statement;

    /** @var Table */
    private $_table;

    /** @var array */
    protected $_options = [];

    /**
     * @param string $name
     * @param string $statement
     * @param mixed[] $options
     */
    public function __construct($name, $statement, array $options = [])
    {
        $this->_setName($name);

        $this->_statement = $statement;

        $this->_options = $options;
    }

    /**
     * Sets the Table instance of the target table
     *
     * @param Table $table Instance of the target table.
     *
     * @return void
     */
    public function setTable(Table $table)
    {
        $this->_table = $table;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @return string
     */
    public function getStatement()
    {
        return $this->_statement;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function addOption($name, $value)
    {
        $this->_options[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasOption($name)
    {
        return isset($this->_options[$name]);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->_options[$name];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
