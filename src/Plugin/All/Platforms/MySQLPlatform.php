<?php

namespace Doctrine\DBAL\Plugin\All\Platforms;

/**
 * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform
 */
trait MySQLPlatform
{
    // @formatter:off,auto-generated:begin
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
    use \Doctrine\DBAL\Plugin\ViewOption\Platforms\MySQLPlatform;
    // @formatter:on,auto-generated:end

    private function _quoteUserHostname(string $userHostname)
    {
        if (! strlen($userHostname)) {
            return $userHostname;
        }

        [$user, $hostname] = explode('@', $userHostname, 2) + [1 => null];

        if ($hostname === null) {
            return $this->quoteSingleIdentifier($user);
        }

        return implode('@', [
            $this->quoteSingleIdentifier($user),
            $this->quoteSingleIdentifier($hostname),
        ]);
    }
}
