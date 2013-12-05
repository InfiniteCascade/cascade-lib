<?php
/**
 * ./app/config/environments/templates/development/params.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

if (!defined('YII_ENV')) {	define('YII_ENV', 'dev');	}

$parent = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . basename(__FILE__);
return array_merge(include($parent), [
	'salt' => '%%_.salt%%',
]);
?>
