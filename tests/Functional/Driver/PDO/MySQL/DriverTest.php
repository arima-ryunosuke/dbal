<?php

namespace Doctrine\DBAL\Tests\Functional\Driver\PDO\MySQL;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\DBAL\Tests\Functional\Driver\AbstractDriverTest;

use function extension_loaded;

class DriverTest extends AbstractDriverTest
{
    protected function setUp(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            self::markTestSkipped('pdo_mysql is not installed.');
        }

        parent::setUp();

        if ($this->connection->getDriver() instanceof Driver) {
            return;
        }

        self::markTestSkipped('pdo_mysql only test.');
    }

    protected function createDriver(): DriverInterface
    {
        return new Driver();
    }
}