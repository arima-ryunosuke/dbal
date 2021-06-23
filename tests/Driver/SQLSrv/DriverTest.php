<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Driver\SQLSrv;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\SQLSrv\Driver;
use Doctrine\DBAL\Tests\Driver\AbstractSQLServerDriverTestCase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('sqlsrv')]
class DriverTest extends AbstractSQLServerDriverTestCase
{
    public function testTimeoutParameter(): void
    {
        $time = microtime(true);
        try {
            $this->driver->connect([
                'host'    => '192.0.2.0',
                'timeout' => 2,
            ]);
            self::fail();
        } catch (\Exception $exception) {
            self::assertThat($exception->getCode(), self::logicalOr(self::equalTo(53), self::equalTo(64), self::equalTo(67), self::equalTo(258)));
            self::assertLessThan(2.5, microtime(true) - $time);
        }
    }

    protected function createDriver(): DriverInterface
    {
        return new Driver();
    }
}
