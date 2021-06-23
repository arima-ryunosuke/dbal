<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Schema\Event;
use Doctrine\DBAL\Schema\Schema;

class EventTest extends AbstractPluginTestCase
{
    public function testPlatform(): void
    {
        $platform = $this->connection->getDatabasePlatform();

        self::assertTrue($platform->supportsEvents());
    }

    public function testDiffEvent(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $comparator    = $schemaManager->createComparator();

        self::assertEquals([], $comparator->diffEvent(
            new Event('hoge', 'select 1', ['option' => 1]),
            new Event('fuga', 'select 1', ['option' => 1]),
        ));
        self::assertEquals([
            [
                'statement' => 'select 1',
            ],
            [
                'statement' => 'select 2',
            ],
        ], $comparator->diffEvent(
            new Event('hoge', 'select 1', ['option' => 1]),
            new Event('fuga', 'select 2', ['option' => 1]),
        ));
        self::assertEquals([
            [
                'options' => ['option' => 1],
            ],
            [
                'options' => ['option' => 2],
            ],
        ], $comparator->diffEvent(
            new Event('hoge', 'select 1', ['option' => 1]),
            new Event('fuga', 'select 1', ['option' => 2]),
        ));
    }

    public function testEvent(): void
    {
        $defoptions = [
            'status'        => 'SLAVESIDE_DISABLED',
            'since'         => '2011-02-03 12:34:56',
            'until'         => '2022-03-04 12:34:56',
            'intervalValue' => '1',
            'intervalField' => 'DAY',
            'completion'    => 'PRESERVE',
            'definer'       => '',
            'comment'       => 'this is comment',
        ];

        $schema1 = new Schema();
        $schema1->addEvent(new Event('nochanged', 'select 1', $defoptions));
        $schema1->addEvent(new Event('altered1', 'select 1', $defoptions));
        $schema1->addEvent(new Event('altered2', 'select 2', $defoptions));
        $schema1->addEvent($droppedEvent = new Event('dropped', 'select 1', $defoptions));

        $schema2 = new Schema();
        $schema2->addEvent(new Event('nochanged', 'select 1', $defoptions));
        $schema2->addEvent($alteredEvent1 = new Event('altered1', 'select 8', $defoptions));
        $schema2->addEvent($alteredEvent2 = new Event('altered2', 'select 9', $defoptions));
        $schema2->addEvent($createdEvent = new Event('created', 'select 1', ['definer' => 'user@localhost'] + $defoptions));

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $diffSchema    = $schemaManager->createComparator()->compareSchemas($schema1, $schema2);

        self::assertFalse($diffSchema->isEmpty());
        self::assertCount(1, $diffSchema->createdEvents);
        self::assertCount(2, $diffSchema->alteredEvents);
        self::assertCount(1, $diffSchema->droppedEvents);
        self::assertSame($createdEvent, $diffSchema->createdEvents[0]);
        self::assertSame($alteredEvent1, $diffSchema->alteredEvents[0]);
        self::assertSame($alteredEvent2, $diffSchema->alteredEvents[1]);
        self::assertSame($droppedEvent, $diffSchema->droppedEvents[0]);

        self::assertEquals([
            <<<SQL
                CREATE DEFINER=`user`@`localhost` EVENT created
                ON SCHEDULE EVERY '1' DAY
                STARTS '2011-02-03 12:34:56'
                ENDS '2022-03-04 12:34:56'
                ON COMPLETION PRESERVE
                SLAVESIDE_DISABLED
                COMMENT 'this is comment'
                DO select 1
                SQL,
            "DROP EVENT dropped",
            <<<SQL
                ALTER EVENT altered1
                ON SCHEDULE EVERY '1' DAY
                STARTS '2011-02-03 12:34:56'
                ENDS '2022-03-04 12:34:56'
                ON COMPLETION PRESERVE
                SLAVESIDE_DISABLED
                COMMENT 'this is comment'
                DO select 8
                SQL,
            <<<SQL
                ALTER EVENT altered2
                ON SCHEDULE EVERY '1' DAY
                STARTS '2011-02-03 12:34:56'
                ENDS '2022-03-04 12:34:56'
                ON COMPLETION PRESERVE
                SLAVESIDE_DISABLED
                COMMENT 'this is comment'
                DO select 9
                SQL,
        ], $platform->getAlterSchemaSQL($diffSchema));
    }

    public function testEventOnline(): void
    {
        $onlineEvent  = new Event('test_onlineevent', 'select 1', [
            'status'        => 'ENABLE',
            'since'         => '2011-02-03 12:34:56',
            'until'         => '2022-03-04 12:34:56',
            'intervalValue' => '1',
            'intervalField' => 'DAY',
            'completion'    => 'PRESERVE',
            'definer'       => 'user@localhost',
            'comment'       => 'this is comment',
        ]);
        $offlineEvent = new Event('test_offlineevent', 'select 2', [
            'status'        => 'DISABLE',
            'since'         => '2011-02-03 12:34:56',
            'until'         => '2022-03-04 12:34:56',
            'intervalValue' => '1',
            'intervalField' => 'DAY',
            'completion'    => 'PRESERVE',
            'definer'       => 'user@localhost',
            'comment'       => 'this is comment',
        ]);

        $schemaManager = $this->connection->createSchemaManager();
        $eventName     = $this->dropAndCreateSchemaObject($onlineEvent);
        $schema        = $schemaManager->introspectSchema();

        self::assertSame($schema, $schema->addEvent($offlineEvent));
        self::assertCount(2, $schema->getEvents());
        self::assertInstanceOf(Event::class, $schema->getEvent($eventName));
        self::assertTrue($schema->hasEvent($eventName));
        self::assertSame($schema, $schema->dropEvent($eventName));
        self::assertCount(1, $schema->getEvents());

        // no error
        $schemaManager->createEvent($offlineEvent);
        $schemaManager->dropEvent($offlineEvent->getName());

        self::assertException('already exists', fn() => $schema->addEvent($offlineEvent));
        self::assertException('There is no', fn() => $schema->getEvent('undefined'));
        self::assertException('There is no', fn() => $schema->dropEvent('undefined'));
    }

    public function testEventStatus(): void
    {
        $this->dropAndCreateSchemaObject(new Event('test_DISABLE', 'select 1', [
            'status'        => 'DISABLE',
            'since'         => '2011-02-03 12:34:56',
            'until'         => '2022-03-04 12:34:56',
            'intervalValue' => '1',
            'intervalField' => 'DAY',
            'completion'    => 'PRESERVE',
            'definer'       => 'user@localhost',
            'comment'       => 'this is comment',
        ]));
        $this->dropAndCreateSchemaObject(new Event('test_SLAVE', 'select 1', [
            'status'        => 'DISABLE ON SLAVE',
            'since'         => '2011-02-03 12:34:56',
            'until'         => '2022-03-04 12:34:56',
            'intervalValue' => '1',
            'intervalField' => 'DAY',
            'completion'    => 'PRESERVE',
            'definer'       => 'user@localhost',
            'comment'       => 'this is comment',
        ]));

        $events = $this->connection->createSchemaManager()->listEvents();
        self::assertEquals('DISABLE', $events['test_disable']->getOption('status'));
        self::assertEquals('DISABLE ON SLAVE', $events['test_slave']->getOption('status'));
    }

    public static function provideInterval(): array
    {
        return [
            'year'          => [
                'field'    => 'year',
                'value'    => '1',
                'expected' => 'P0001Y00M00DT00H00M00S',
            ],
            'quarter'       => [
                'field'    => 'quarter',
                'value'    => '5',
                'expected' => 'P0000Y15M00DT00H00M00S',
            ],
            'month'         => [
                'field'    => 'month',
                'value'    => '13',
                'expected' => 'P0000Y13M00DT00H00M00S',
            ],
            'week'          => [
                'field'    => 'week',
                'value'    => '10',
                'expected' => 'P0000Y00M70DT00H00M00S',
            ],
            'day'           => [
                'field'    => 'day',
                'value'    => '33',
                'expected' => 'P0000Y00M33DT00H00M00S',
            ],
            'hour'          => [
                'field'    => 'hour',
                'value'    => '32',
                'expected' => 'P0000Y00M00DT32H00M00S',
            ],
            'minute'        => [
                'field'    => 'minute',
                'value'    => '61',
                'expected' => 'P0000Y00M00DT00H61M00S',
            ],
            'second'        => [
                'field'    => 'second',
                'value'    => '62',
                'expected' => 'P0000Y00M00DT00H00M62S',
            ],
            'year_month'    => [
                'field'    => 'year_month',
                'value'    => '1-12',
                'expected' => 'P0002Y00M00DT00H00M00S',
            ],
            'day_hour'      => [
                'field'    => 'day_hour',
                'value'    => '32 25',
                'expected' => 'P0000Y00M33DT01H00M00S',
            ],
            'day_minute'    => [
                'field'    => 'day_minute',
                'value'    => '32 25:61',
                'expected' => 'P0000Y00M33DT02H01M00S',
            ],
            'day_second'    => [
                'field'    => 'day_second',
                'value'    => '32 25:61:62',
                'expected' => 'P0000Y00M33DT02H02M02S',
            ],
            'hour_minute'   => [
                'field'    => 'hour_minute',
                'value'    => '25:61',
                'expected' => 'P0000Y00M00DT26H01M00S',
            ],
            'hour_second'   => [
                'field'    => 'hour_second',
                'value'    => '25:61:62',
                'expected' => 'P0000Y00M00DT26H02M02S',
            ],
            'minute_second' => [
                'field'    => 'minute_second',
                'value'    => '61:62',
                'expected' => 'P0000Y00M00DT00H62M02S',
            ],
        ];
    }

    /**
     * @dataProvider provideInterval
     */
    public function testEventInterval($field, $value, $expected): void
    {
        $this->dropAndCreateSchemaObject(new Event("test_e{$this->dataName()}", 'select 1', [
            'status'        => 'DISABLE',
            'since'         => '2011-02-03 12:34:56',
            'until'         => '2022-03-04 12:34:56',
            'intervalValue' => $value,
            'intervalField' => $field,
            'completion'    => 'PRESERVE',
            'definer'       => 'user@localhost',
            'comment'       => 'this is comment',
        ]));

        $events = $this->connection->createSchemaManager()->listEvents();
        self::assertEquals($expected, $events["test_e{$this->dataName()}"]->getOption('interval'));
    }
}
