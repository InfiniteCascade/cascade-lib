<?php
/**
 * ./app/config/environments/common/console.php.
 *
 * @author Jacob Morrison <jacob@tealcascade.com>
 */
defined('IS_CONSOLE') || define('IS_CONSOLE', true);
$config = include __DIR__ . DIRECTORY_SEPARATOR . 'base.php';
$config['controllerNamespace'] = 'cascade\commands';
$config['controllerMap'] = [
    'migrate' => 'teal\console\controllers\MigrateController',
    'phpDoc' => 'teal\console\controllers\PhpDocController',
];
unset($config['modules']['debug']);

return $config;
