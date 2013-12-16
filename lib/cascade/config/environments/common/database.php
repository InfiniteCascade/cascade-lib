<?php
/**
 * ./app/config/environments/common/database.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */


return array(
	'class' => '\infinite\db\Connection',
	'dsn' => 'mysql:host='.INFINITE_APP_DATABASE_HOST.';port='.INFINITE_APP_DATABASE_PORT.';dbname='.INFINITE_APP_DATABASE_DBNAME.'',
	'emulatePrepare' => true,
	'username' => INFINITE_APP_DATABASE_USERNAME,
	'password' => INFINITE_APP_DATABASE_PASSWORD,
	'charset' => 'utf8',
	'enableSchemaCache' => true

);
?>
