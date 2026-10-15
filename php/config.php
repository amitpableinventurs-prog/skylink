<?php

/**
 * MySQL connection for the blog + admin panel.
 * Defaults below are for local XAMPP (root, no password).
 * On the live server, create php/config.local.php (not tracked in git) returning the
 * same array with the live database details — it overrides these defaults.
 */
$config = [
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'skylinkmedia',
    'db_user' => 'root',
    'db_pass' => '',
];

if (is_file(__DIR__ . '/config.local.php')) {
    $config = array_merge($config, require __DIR__ . '/config.local.php');
}

return $config;
