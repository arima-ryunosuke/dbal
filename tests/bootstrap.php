<?php

require_once __DIR__ . '/../vendor/autoload.php';

// for pallarel test
$lock = fopen(sys_get_temp_dir() . '/dbal.lock', 'c+');
flock($lock, LOCK_EX);
(new class { use Doctrine\DBAL\Plugin\Pluggable; })::generateAll();
flock($lock, LOCK_UN);
fclose($lock);

// detect test target
$test_dsn = (function () {
    $rdbms    = getenv('TEST_TARGET') ?: getenv('TEST_TARGET_DEFAULT');
    $test_dsn = getenv("test_dsn_$rdbms");
    if ($rdbms && ! $test_dsn) {
        echo "env 'test_dsn_$rdbms' is not defined.\n";
        exit(1);
    }
    return $test_dsn;
})();

// set GLOBAL connection parameters
$params = (function ($test_dsn) {
    $parts = parse_url($test_dsn);

    $parts['driver'] = strtr($parts['scheme'], ['pdo-' => 'pdo_']);
    $parts['dbname'] = ltrim($parts['path'], '/');
    $parts           += [
        'host'     => null,
        'port'     => null,
        'user'     => null,
        'password' => $parts['pass'] ?? null,
    ];

    parse_str($parts['query'] ?? '', $params);
    parse_str($parts['fragment'] ?? '', $options);

    foreach (['db', 'tmpdb'] as $prefix) {
        $GLOBALS["{$prefix}_type"]     = $parts['driver'];
        $GLOBALS["{$prefix}_driver"]   = $parts['driver'];
        $GLOBALS["{$prefix}_host"]     = $parts['host'];
        $GLOBALS["{$prefix}_port"]     = $parts['port'];
        $GLOBALS["{$prefix}_user"]     = $parts['user'];
        $GLOBALS["{$prefix}_password"] = $parts['password'];
        $GLOBALS["{$prefix}_dbname"]   = $parts['dbname'];
        foreach ($params as $key => $value) {
            $GLOBALS["{$prefix}_{$key}"] = $value;
        }
        foreach ($options as $key => $value) {
            $GLOBALS["{$prefix}_driver_option_{$key}"] = $value;
        }
    }

    $GLOBALS["tmpdb_dbname"] .= '_tmp';

    return $parts;
})($test_dsn);

// create test database
(function ($params) {
    unset($params['dbname']);
    $connection = \Doctrine\DBAL\DriverManager::getConnection($params);
    $schemar    = $connection->createSchemaManager();
    $databases  = $schemar->listDatabases();

    foreach ([$GLOBALS['db_dbname'], $GLOBALS['tmpdb_dbname']] as $dbname) {
        if (! in_array($dbname, $databases)) {
            $schemar->createDatabase($dbname);
        }
    }

    $connection->close();
})($params);

// report Plugin history
(function () {
    if (getenv('TEST_REPORT')) {
        $GLOBALS['__callHistories'] = [];
        register_shutdown_function(function () {
            echo "# Plugin Call Histories\n";
            echo json_encode($GLOBALS['__callHistories'], JSON_PRETTY_PRINT);
        });
    }
})();
