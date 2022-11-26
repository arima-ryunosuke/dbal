<?php

namespace Doctrine\DBAL\Schema;

/**
 * Representation of a Database View.
 */
class View extends AbstractAsset
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\All\Schema\View;

    /** @var string */
    private $sql;

    /**
     * @param string $name
     * @param string $sql
     */
    public function __construct($name, $sql, $options = [])
    {
        $this->_setName($name);
        $this->sql = $sql;

        extract($this->intercept(get_defined_vars()));
    }

    /** @return string */
    public function getSql()
    {
        return $this->sql;
    }
}
