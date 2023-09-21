<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\SchemaConfig
 */
trait SchemaConfig
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\ImplicitIndex\Schema\SchemaConfig;
}
