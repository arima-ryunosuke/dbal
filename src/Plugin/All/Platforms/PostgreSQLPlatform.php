<?php

namespace Doctrine\DBAL\Plugin\All\Platforms;

/**
 * @used-by \Doctrine\DBAL\Platforms\PostgreSQLPlatform
 */
trait PostgreSQLPlatform
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Routine\Platforms\PostgreSQLPlatform;
    use \Doctrine\DBAL\Plugin\Trigger\Platforms\PostgreSQLPlatform;
}
