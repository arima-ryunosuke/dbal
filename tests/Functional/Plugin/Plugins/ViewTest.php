<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin\Plugins;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Types\Type;

class ViewTest extends AbstractPluginTestCase
{
    public function testOptions(): void
    {
        $view = new View('hoge', 'select 1', ['option' => 1]);

        self::assertTrue($view->hasOption('option'));
        self::assertFalse($view->hasOption('hogera'));

        $view->addOption('hogera', 2);
        self::assertEquals('2', $view->getOption('hogera'));

        self::assertEquals([
            'option' => 1,
            'hogera' => 2,
        ], $view->getOptions());
    }

    public function testDiffView(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $comparator    = $schemaManager->createComparator();

        self::assertEquals([], $comparator->diffView(
            new View('hoge', 'select 1', ['option' => 1, 'updatable' => true]),
            new View('fuga', 'select 1', ['option' => 1, 'updatable' => true]),
        ));
        self::assertEquals([], $comparator->diffView(
            new View('hoge', 'select 1', ['option' => 1, 'updatable' => true]),
            new View('fuga', 'select 1', ['option' => 1, 'updatable' => false]),
        ));
        self::assertEquals([
            [
                'sql' => '1',
            ],
            [
                'sql' => '2',
            ],
        ], $comparator->diffView(
            new View('hoge', 'select 1', ['option' => 1, 'updatable' => true]),
            new View('fuga', 'select 2', ['option' => 1, 'updatable' => true]),
        ));
        self::assertEquals([
            [
                'options' => ['option' => 1],
            ],
            [
                'options' => ['option' => 2],
            ],
        ], $comparator->diffView(
            new View('hoge', 'select 1', ['option' => 1, 'updatable' => true]),
            new View('fuga', 'select 1', ['option' => 2, 'updatable' => true]),
        ));
    }

    public function testView(): void
    {
        $schema1 = new Schema();
        $schema1->addView($droppedView = new View('droppedView', 'select 1'));
        $schema1->addView(new View('alteredView1', 'select 2', ['checkOption' => 'CASCADE']));
        $schema1->addView(new View('alteredView2', 'select 2', ['checkOption' => 'CASCADE']));
        $schema1->addView(new View('nochangedView', 'select 5', ['checkOption' => 'CASCADE']));

        $schema2 = new Schema();
        $schema2->addView($createdView = new View('createdView', 'select 3', ['checkOption' => 'CASCADE']));
        $schema2->addView($alteredView1 = new View('alteredView1', 'select 9', ['checkOption' => 'CASCADE']));
        $schema2->addView($alteredView2 = new View('alteredView2', 'select 2', ['checkOption' => 'LOCAL']));
        $schema2->addView(new View('nochangedView', 'select 5', ['checkOption' => 'CASCADE']));

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $diffSchema    = $schemaManager->createComparator()->compareSchemas($schema1, $schema2);

        self::assertFalse($diffSchema->isEmpty());
        self::assertEquals(1, count($diffSchema->createdViews));
        self::assertEquals(2, count($diffSchema->alteredViews));
        self::assertEquals(1, count($diffSchema->droppedViews));
        self::assertSame($createdView, $diffSchema->createdViews[0]);
        self::assertSame($alteredView1, $diffSchema->alteredViews[0]);
        self::assertSame($alteredView2, $diffSchema->alteredViews[1]);
        self::assertSame($droppedView, $diffSchema->droppedViews[0]);

        if ($platform instanceof MySQLPlatform) {
            self::assertEquals([
                'CREATE VIEW createdView AS select 3 WITH CASCADE CHECK OPTION',
                'DROP VIEW droppedView',
                'ALTER VIEW alteredView1 AS select 9 WITH CASCADE CHECK OPTION',
                'ALTER VIEW alteredView2 AS select 2 WITH LOCAL CHECK OPTION',
            ], $platform->getAlterSchemaSQL($diffSchema));
        } else {
            self::assertEquals([
                'CREATE VIEW createdView AS select 3',
                'DROP VIEW droppedView',
                'DROP VIEW alteredView1',
                'CREATE VIEW alteredView1 AS select 9',
                'DROP VIEW alteredView2',
                'CREATE VIEW alteredView2 AS select 2',
            ], $platform->getAlterSchemaSQL($diffSchema));
        }
    }

    public function testViewOnline(): void
    {
        $onlineView  = new View('test_onlineview', 'select 1 as a');
        $offlineView = new View('test_offlineview', 'select 2 as b');

        $schemaManager = $this->connection->createSchemaManager();
        $viewName      = $this->dropAndCreateSchemaObject($onlineView);
        $schema        = $schemaManager->introspectSchema();

        self::assertSame($schema, $schema->addView($offlineView));
        self::assertCount(2, $schema->getViews());
        self::assertInstanceOf(View::class, $schema->getView($viewName));
        self::assertTrue($schema->hasView($viewName));
        self::assertSame($schema, $schema->dropView($viewName));
        self::assertCount(1, $schema->getViews());

        self::assertException('already exists', fn() => $schema->addView($offlineView));
        self::assertException('There is no', fn() => $schema->getView('undefined'));
        self::assertException('There is no', fn() => $schema->dropView('undefined'));
    }

    public function testIntrospectViewAsTable(): void
    {
        $table = new Table('test_introspect_view_table');
        $table->addColumn('foo_id', 'integer');
        $table->addColumn('bar_id', 'integer');

        $view = new View('test_introspect_view', "SELECT *, 'hoge' AS static_str FROM test_introspect_view_table");

        $platform      = $this->connection->getDatabasePlatform();
        $schemaManager = $this->connection->createSchemaManager();
        $schemaManager->createTable($table);
        $schemaManager->createView($view);

        $test_introspect_view = $schemaManager->introspectViewAsTable('test_introspect_view');
        self::assertCount(3, $test_introspect_view->getColumns());
        self::assertEquals('integer', Type::getTypeRegistry()->lookupName($test_introspect_view->getColumn('foo_id')->getType()));
        self::assertEquals('integer', Type::getTypeRegistry()->lookupName($test_introspect_view->getColumn('bar_id')->getType()));
        self::assertThat(Type::getTypeRegistry()->lookupName($test_introspect_view->getColumn('static_str')->getType()), self::logicalOr(
            self::equalTo('string'),
            self::equalTo('text'),
        ));

        if ($platform instanceof MySQLPlatform) {
            self::assertEquals("NONE", $test_introspect_view->getOption('view_options')['checkOption']);
        }

        $schemaManager->dropView($view->getName());
        $schemaManager->dropTable($table->getName());

        $this->expectException(Exception::class);
        $schemaManager->introspectViewAsTable('test_undefined_view');
    }
}
