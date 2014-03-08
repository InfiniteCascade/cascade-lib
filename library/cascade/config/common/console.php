<?php
/**
 * ./app/config/environments/common/console.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */
defined('IS_CONSOLE') || define('IS_CONSOLE', true);
$config = include(__DIR__ . DIRECTORY_SEPARATOR . 'base.php');
$config['controllerNamespace'] = 'cascade\\commands';
$config['controllerMap'] = [
	'migrate' => 'infinite\\console\\controllers\\MigrateController'
];
unset($config['modules']['debug']);
$config['controllerNamespace'] = 'cascade\\commands';
return $config;