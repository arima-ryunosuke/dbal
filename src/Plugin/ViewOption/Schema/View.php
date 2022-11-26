<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Schema;

trait View
{
    /** @var mixed[] */
    protected $_options = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\View::__construct()
     */
    protected function intercept__constructByViewOption($options)
    {
        $this->_options = $options;
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
