#!/usr/bin/env php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\MySql\SpatialType;

// common connection config
$config = array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'demo',
    'password' => '',
    'charset' => 'utf8'
);

// import sql files
$conn = DriverManager::getConnection($config);
$dir = realpath(__DIR__ . '/sql');
$conn->exec(file_get_contents("$dir/from.sql"));
$conn->exec(file_get_contents("$dir/to.sql"));

// connection from and to
$config['dbname'] = 'test_demo_from';
$from = DriverManager::getConnection($config);
$config['dbname'] = 'test_demo_to';
$to = DriverManager::getConnection($config);

// register SpatialTypes
$types = SpatialType::addSpatialTypes();
foreach ($types as $dbType => $type) {
    $from->getDatabasePlatform()->registerDoctrineTypeMapping($dbType, $type->getName());
    $to->getDatabasePlatform()->registerDoctrineTypeMapping($dbType, $type->getName());
}

// generate ddl
$fromSchema = $from->getSchemaManager()->createSchema();
$toSchema = $to->getSchemaManager()->createSchema();
$sqls = $fromSchema->getMigrateToSql($toSchema, $from->getDatabasePlatform());

// simple format and print
echo implode(PHP_EOL, array_map(function ($sql)
{
    $sql = preg_replace('/(CREATE (TABLE|(UNIQUE )?INDEX).+?\()(.+)/su', "$1\n  $4", $sql);
    $sql = preg_replace('/(CREATE (TABLE|(UNIQUE )?INDEX).+)(\))/su', "$1\n$4", $sql);
    
    $sql = preg_replace('/(ALTER TABLE .+?) ((ADD|DROP|CHANGE|MODIFY).+)/su', "$1\n  $2", $sql);
    
    $sql = preg_replace('/(, )([^\\d])/u', ",\n  $2", $sql);
    return $sql;
}, $sqls));
