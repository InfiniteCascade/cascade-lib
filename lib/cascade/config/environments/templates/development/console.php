<?php
/**
 * ./app/config/environments/templates/development/console.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */


$parent = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . basename(__FILE__);
$config = include $parent;
$config = array_merge($config, [
		'id' => '%%_.application_id%%',
		'name' => '%%general.application_name%%',
	]);

return $config;
?>
