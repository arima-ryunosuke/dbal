<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\AbstractNamedObject;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\Name\Parser\OptionallyQualifiedNameParser;
use Doctrine\DBAL\Schema\Name\Parsers;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\View;

class UtilityTest extends AbstractPluginTestCase
{
    public function testMySQLPlatform(): void
    {
        $platform = new class extends MySqlPlatform {
            use \Doctrine\DBAL\Plugin\Utility\Platforms\MySQLPlatform {
                _quoteUserHostname as public quoteUserHostname;
            }
        };

        self::assertEquals('', $platform->quoteUserHostname(''));
        self::assertEquals('`user`', $platform->quoteUserHostname('user'));
        self::assertEquals('`user`@`hostname`', $platform->quoteUserHostname('user@hostname'));
    }

    public function testAbstractNamedObject(): void
    {
        $nameObject = new class('dummynamespace.hoge') extends AbstractNamedObject {
            use \Doctrine\DBAL\Plugin\Utility\Schema\AbstractNamedObject;

            protected function getNameParser(): OptionallyQualifiedNameParser
            {
                return Parsers::getOptionallyQualifiedNameParser();
            }
        };

        self::assertEquals('dummynamespace', $nameObject->getNamespace());
        self::assertEquals('hoge', $nameObject->getLocalName());
    }

    public function testSchema(): void
    {
        $schema = new class extends Schema {
            use \Doctrine\DBAL\Plugin\Utility\Schema\Schema {
                _createNamespaceBy as public createNamespaceBy;
            }

            public function resolveName(AbstractAsset $asset): AbstractAsset
            {
                if ($asset->getNamespaceName() === null) {
                    $defaultNamespaceName = $this->getName();

                    if ($defaultNamespaceName !== '') {
                        return new Identifier($defaultNamespaceName . '.' . $asset->getName());
                    }
                }

                return $asset;
            }
        };

        $schema->createNamespaceBy(new View('dummynamespace.dummyview', 'select 1'));
        self::assertEquals(['dummynamespace'], $schema->getNamespaces());
    }
}
