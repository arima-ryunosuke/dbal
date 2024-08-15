<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Driver\Mysqli;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use Doctrine\DBAL\Tests\Driver\AbstractDriverTestCase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('mysqli')]
class DriverTest extends AbstractDriverTestCase
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
            self::assertEquals(2002, $exception->getCode());
            self::assertLessThan(2.5, microtime(true) - $time);
        }
    }

    protected function createDriver(): DriverInterface
    {
        return new Driver();
    }
}
