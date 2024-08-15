<?php

namespace Doctrine\DBAL\Tests\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractSQLServerDriver\Exception\PortWithoutHost;
use Doctrine\DBAL\Driver\API\ExceptionConverter as ExceptionConverterInterface;
use Doctrine\DBAL\Driver\API\SQLSrv\ExceptionConverter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SQLServer2012Platform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\SQLServerSchemaManager;

/** @extends AbstractDriverTestCase<SQLServerPlatform> */
abstract class AbstractSQLServerDriverTestCase extends AbstractDriverTestCase
{
    protected function createPlatform(): AbstractPlatform
    {
        return new SQLServer2012Platform();
    }

    protected function createSchemaManager(Connection $connection): AbstractSchemaManager
    {
        return new SQLServerSchemaManager(
            $connection,
            $this->createPlatform(),
        );
    }

    protected function createExceptionConverter(): ExceptionConverterInterface
    {
        return new ExceptionConverter();
    }

    public function testPortWithoutHost(): void
    {
        $this->expectException(PortWithoutHost::class);
        $this->driver->connect(['port' => 1433]);
    }

    public function testTimeoutParameter(): void
    {
        if (!extension_loaded('sqlsrv') || $this->driver instanceof \Doctrine\DBAL\Driver\PDO\SQLSrv\Driver) {
            self::markTestSkipped();
        }

        $time = microtime(true);
        try {
            $this->driver->connect([
                'host'    => '192.0.2.0',
                'timeout' => 2,
            ]);
            self::fail();
        } catch (\Exception $exception) {
            self::assertEquals(53, $exception->getCode());
            self::assertLessThan(2.5, microtime(true) - $time);
        }
    }
}
