<?php

namespace Doctrine\DBAL\Tests\Platforms\MySQL;

use Doctrine\DBAL\Platforms\MySQL\Comparator;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Tests\Schema\ComparatorTest as BaseComparatorTest;
use Doctrine\DBAL\Types\Type;

class ComparatorTest extends BaseComparatorTest
{
    protected function setUp(): void
    {
        $this->comparator = new Comparator(new MySQLPlatform());
    }

    /* ryunosuke appendix */

    public function testCompareChangedColumnsChangeTextLength(): void
    {
        $column1 = new Column('textfield1', Type::getType('text'), ['Length' => 128]);
        $column2 = new Column('textfield1', Type::getType('text'), ['Length' => 65536]);
        $column3 = new Column('textfield1', Type::getType('text'), ['Length' => 65536]);

        self::assertEquals(['length'], $this->comparator->diffColumn($column1, $column2));
        self::assertEquals([], $this->comparator->diffColumn($column1, $column1));
        self::assertEquals([], $this->comparator->diffColumn($column2, $column3));
    }
}
