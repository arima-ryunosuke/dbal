<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;

class ForeignKeyConstraintOptionTest extends AbstractPluginTestCase
{
    protected bool $autoskip = false;

    public function testOption(): void
    {
        $fk = new ForeignKeyConstraint(['c1', 'c2'], 'table1', ['c1', 'c2'], 'fk_dummy', ['onDelete' => 'CASCADE']);

        self::assertEquals('CASCADE', $fk->onDelete());

        $fk->addOption('onDelete', 'RESTRICT');
        $fk->addOption('hoge', 'HOGE');

        self::assertEquals(null, $fk->onDelete());
        self::assertEquals('HOGE', $fk->getOption('hoge'));
    }
}
