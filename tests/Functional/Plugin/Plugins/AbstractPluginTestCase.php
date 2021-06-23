<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Tests\FunctionalTestCase;
use PHPUnit\Framework\Error\Error;
use ReflectionClass;

abstract class AbstractPluginTestCase extends FunctionalTestCase
{
    protected bool $autoskip = true;

    protected function setUp(): void
    {
        parent::setUp();

        putenv("PluginTest=" . (int) ($this->connection->getDatabasePlatform() instanceof MySQLPlatform));

        if ($this->autoskip) {
            $pluginName = preg_replace('#Test$#', '', (new ReflectionClass($this))->getShortName());

            $traits = array_merge(
                $this->getAllTrait($this->connection->getDatabasePlatform()),
            );

            $traits = array_filter($traits, fn($t) => strpos($t, 'Abstract') === false && strpos($t, "Plugin\\$pluginName") !== false);

            if (! $traits) {
                $this->markTestSkipped();
            }
        }
    }

    protected function tearDown(): void
    {
        putenv("PluginTest=0");

        parent::tearDown();
    }

    private function getAllTrait($class)
    {
        $traits = class_uses($class);

        foreach (class_parents($class) as $parent) {
            $traits += class_uses($parent);
        }

        do {
            $count = count($traits);
            foreach ($traits as $trait) {
                $traits += class_uses($trait);
            }
        } while ($count !== count($traits));

        return $traits;
    }

    function dropAndCreateSchemaObject(AbstractAsset $object)
    {
        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();

        $objectType = (new ReflectionClass($object))->getShortName();
        $objectName = $object->getQuotedName($platform);

        try {
            $args = [$objectName];
            if ($object instanceof Routine) {
                $args[] = $object->getType();
            }
            $schemaManager->{"drop$objectType"}(...$args);
        } catch (Exception $e) {
        }

        $schemaManager->{"create$objectType"}($object);

        return $objectName;
    }

    public static function assertException($e, $callback)
    {
        if (is_string($e)) {
            $e = new \Exception($e);
        }

        try {
            $callback(...array_slice(func_get_args(), 2));
        } catch (Error $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            self::assertInstanceOf(get_class($e), $ex);
            self::assertEquals($e->getCode(), $ex->getCode());
            if (strlen($e->getMessage()) > 0) {
                self::assertStringContainsString($e->getMessage(), $ex->getMessage());
            }
            return;
        }
        self::fail(get_class($e) . ' is not thrown.');
    }
}
