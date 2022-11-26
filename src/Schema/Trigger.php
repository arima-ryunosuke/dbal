<?php

namespace Doctrine\DBAL\Schema;

use Doctrine\DBAL\Schema\Name\Parser\OptionallyQualifiedNameParser;
use Doctrine\DBAL\Schema\Name\Parsers;

/**
 * Representation of a Database Trigger.
 */
class Trigger extends AbstractNamedObject
{
    /** @var string */
    private $_tableName;

    /** @var string */
    private $_statement;

    /** @var array */
    protected $_options = [];

    /**
     * @param string $name
     * @param string $statement
     * @param string $tableName
     * @param mixed[] $options
     */
    public function __construct($name, $statement, $tableName, array $options = [])
    {
        parent::__construct($name);

        $this->_statement = $statement;
        $this->_tableName = $tableName;
        $this->_options   = $options;
    }

    protected function getNameParser(): OptionallyQualifiedNameParser
    {
        return Parsers::getOptionallyQualifiedNameParser();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
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
        return $this->_options[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
