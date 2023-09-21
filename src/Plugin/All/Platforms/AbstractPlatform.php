<?php

namespace Doctrine\DBAL\Plugin\All\Platforms;

/**
 * @used-by \Doctrine\DBAL\Platforms\AbstractPlatform
 */
trait AbstractPlatform
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\AutoType\Platforms\AbstractPlatform;
    use \Doctrine\DBAL\Plugin\CustomType\Platforms\AbstractPlatform;
    use \Doctrine\DBAL\Plugin\DiffComment\Platforms\AbstractPlatform;
    use \Doctrine\DBAL\Plugin\Event\Platforms\AbstractPlatform;
    use \Doctrine\DBAL\Plugin\Routine\Platforms\AbstractPlatform;
    use \Doctrine\DBAL\Plugin\Trigger\Platforms\AbstractPlatform;
    use \Doctrine\DBAL\Plugin\View\Platforms\AbstractPlatform;
}
