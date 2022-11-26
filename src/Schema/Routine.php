<?php

namespace Doctrine\DBAL\Schema;

use Doctrine\DBAL\Schema\Name\Parser\OptionallyQualifiedNameParser;
use Doctrine\DBAL\Schema\Name\Parsers;

/**
 * Representation of a Database Routine.
 */
class Routine extends AbstractNamedObject
{
    public const TYPE_PROCEDURE = 'PROCEDURE';
    public const TYPE_FUNCTION  = 'FUNCTION';

    /** @var string */
    private $_statement;

    /** @var array */
    protected $_options = [];

    /**
     * @param string $name
     * @param string $statement
     * @param mixed[] $options
     */
    public function __construct($name, $statement, array $options = [])
    {
        parent::__construct($name);

        $this->_statement = $statement;

        $this->_options = $options;
    }

    protected function getNameParser(): OptionallyQualifiedNameParser
    {
        return Parsers::getOptionallyQualifiedNameParser();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return strtoupper($this->getOption('type'));
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
     * @param mixed $value
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
