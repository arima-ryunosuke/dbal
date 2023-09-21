<?php

namespace Doctrine\DBAL\Plugin\All\Platforms;

/**
 * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform
 */
trait MySQLPlatform
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\CustomType\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\DiffComment\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\Event\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\ExpressionDefaultColumn\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\GeneratedColumn\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\IndexOption\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\PositionalColumn\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\Routine\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\TableOption\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\Trigger\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\Utility\Platforms\MySQLPlatform;
    use \Doctrine\DBAL\Plugin\View\Platforms\MySQLPlatform;
}
