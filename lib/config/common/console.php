<?php
/**
 * ./app/config/environments/common/console.php.
 *
 * @author Jacob Morrison <jacob@canis.io>
 */
defined('IS_CONSOLE') || define('IS_CONSOLE', true);
$config = include __DIR__ . DIRECTORY_SEPARATOR . 'base.php';
$config['controllerNamespace'] = 'cascade\commands';
$config['controllerMap'] = [
    'migrate' => 'canis\console\controllers\MigrateController',
    'phpDoc' => 'canis\console\controllers\PhpDocController',
];
unset($config['modules']['debug']);
$config['controllerNamespace'] = 'cascade\commands';

return $config;
