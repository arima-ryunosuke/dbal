<?php

namespace Doctrine\DBAL\Plugin\ForeignKeyConstraintOption\Schema;

trait ForeignKeyConstraint
{
    public function addOption($name, $value)
    {
        $this->_options[$name] = $value;

        return $this;
    }
}
