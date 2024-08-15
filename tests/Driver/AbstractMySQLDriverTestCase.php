<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\API\MySQL;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\MySQLSchemaManager;

/** @extends AbstractDriverTestCase<MySQLPlatform> */
abstract class AbstractMySQLDriverTestCase extends AbstractDriverTestCase
{
    protected function createPlatform(): AbstractPlatform
    {
        return new MySQLPlatform();
    }

    protected function createSchemaManager(Connection $connection): AbstractSchemaManager
    {
        return new MySQLSchemaManager(
            $connection,
            $this->createPlatform(),
        );
    }

    protected function createExceptionConverter(): ExceptionConverter
    {
        return new MySQL\ExceptionConverter();
    }

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
}
