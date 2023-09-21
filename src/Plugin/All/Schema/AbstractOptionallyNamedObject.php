<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\AbstractOptionallyNamedObject
 */
trait AbstractOptionallyNamedObject
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Utility\Schema\AbstractOptionallyNamedObject;
}
