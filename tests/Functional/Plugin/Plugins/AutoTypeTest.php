<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Types\Type;

class AutoTypeTest extends AbstractPluginTestCase
{
    protected bool $autoskip = false;

    public function testAutoType(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $platform->enableAutoType(true);

        $name = $platform->getDoctrineTypeMapping('hoge');
        self::assertEquals('hoge', $name);

        $type = Type::getType('hoge');
        self::assertEquals('hoge', Type::getTypeRegistry()->lookupName($type));
        self::assertEquals('HOGE(misc)', $type->getSQLDeclaration(['type-declaration' => 'HOGE(misc)'], $platform));

        $platform->enableAutoType(false);
    }
}
