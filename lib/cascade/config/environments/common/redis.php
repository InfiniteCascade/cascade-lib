<?php
/**
 * ./app/config/environments/common/cache.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */


return [
	'class' => 'yii\redis\Connection',
	'hostname' => '127.0.0.1',
	'port' => 6379,
	'database' => 0
];
?>
