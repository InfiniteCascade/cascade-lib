<?php
/**
 * ./app/config/environments/common/console.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

$config = include(__DIR__ . DIRECTORY_SEPARATOR . 'base.php');
$config['controllerNamespace'] = 'cascade\\commands';
//$config['controllerPath'] = '@cascade/commands';
return $config;