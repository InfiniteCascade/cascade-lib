<?php
/**
 * ./app/config/environments/common/console.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */
$modules = include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'modules.php');
unset($modules['debug']);
return [
	'id' => 'cascade',
	'name' => 'Cascade',
	'basePath' => INFINITE_APP_APP_PATH,
	'vendorPath' => INFINITE_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'vendor',
	'preload' => array('log', 'collectors'),
	'language' => 'en',
	'controllerPath' => '@cascade/commands',
	'controllerNamespace' => 'cascade\commands',
	'controllerMap' => array(
		'migrate' => '\infinite\console\controllers\MigrateController'
	),
	'modules' => $modules,
	'extensions' => include(INFINITE_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'yiisoft'. DIRECTORY_SEPARATOR . 'extensions.php'),
	
	// application components
	'components' => [ 
		'db' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "database.php"),
		'redis' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'redis.php'),
		'cache' => ['class' => '\yii\redis\Cache'],
		'collectors' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'collectors.php'),
		'gk' => [
			'class' => 'infinite\\security\\Gatekeeper',
			'acaClass' => 'cascade\\models\\Aca',
			'aclClass' => 'cascade\\models\\Acl',
			'aclRoleClass' => 'cascade\\models\\AclRole',
			'groupClass' => 'cascade\\models\\Group',
			'registryClass' => 'cascade\\models\\Registry',
			'userClass' => 'cascade\\models\\User'
		],
		'log' => [
			'class' => 'yii\log\Logger',
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
	],
	'params' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "params.php"),
];