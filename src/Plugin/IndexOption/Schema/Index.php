<?php

namespace Doctrine\DBAL\Plugin\IndexOption\Schema;

trait Index
{
    public function addOption($name, $value)
    {
        $this->options[strtolower($name)] = $value;

        return $this;
    }
}
