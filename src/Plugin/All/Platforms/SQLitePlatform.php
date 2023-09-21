<?php

namespace Doctrine\DBAL\Plugin\All\Platforms;

/**
 * @used-by \Doctrine\DBAL\Platforms\SQLitePlatform
 */
trait SQLitePlatform
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Trigger\Platforms\SQLitePlatform;
}
