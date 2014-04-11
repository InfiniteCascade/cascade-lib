<?php
defined('INFINITE_ROLE_LEVEL_OWNER') || define('INFINITE_ROLE_LEVEL_OWNER', 600); // owner levels: 501-600
defined('INFINITE_ROLE_LEVEL_MANAGER') || define('INFINITE_ROLE_LEVEL_MANAGER', 500); // manager levels: 401-500
defined('INFINITE_ROLE_LEVEL_EDITOR') || define('INFINITE_ROLE_LEVEL_EDITOR', 400); // editor levels: 301-400
defined('INFINITE_ROLE_LEVEL_COMMENTER') || define('INFINITE_ROLE_LEVEL_COMMENTER', 300); // commenter levels: 201-300; doesn't exist in system
defined('INFINITE_ROLE_LEVEL_VIEWER') || define('INFINITE_ROLE_LEVEL_VIEWER', 200); // viewer levels: 101-200
defined('INFINITE_ROLE_LEVEL_BROWSER') || define('INFINITE_ROLE_LEVEL_BROWSER', 100); // viewer levels: 1-100

$base = [
	'id' => 'cascade',
	'name' => 'Cascade',
	'basePath' => INFINITE_APP_PATH,
	'vendorPath' => INFINITE_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'vendor',
	'runtimePath' => INFINITE_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'runtime',
	'bootstrap' => ['collectors'],
	'language' => 'en',
	'modules' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'modules.php'),
	'extensions' => include(INFINITE_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'yiisoft'. DIRECTORY_SEPARATOR . 'extensions.php'),
	
	// application components
	'components' => [
		'classes' => [
			'class' => 'cascade\\components\\base\\ClassManager',
		],
		'db' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "database.php"),
		'gk' => [
			'class' => 'cascade\\components\\security\\Gatekeeper',
			'authority' => [
				'type' => 'Individual',
			]
		],
		'redis' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'redis.php'),
		'collectors' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'collectors.php'),
		//'cache' => ['class' => 'yii\\redis\\Cache'],
		'cache' => ['class' => 'yii\\caching\\DummyCache'],
		'errorHandler' => [
			'discardExistingOutput' => false
		],
		'view' => [
			'class' => 'cascade\\components\\web\\View',
		],
		'response' => [
			'class' => 'infinite\\web\\Response'
		],
		
		'log' => [
			'traceLevel' => YII_DEBUG ? 7 : 0,
			'targets' => [
				[
					'class' => 'yii\\log\\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
	],
	'params' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "params.php"),
];
if (!extension_loaded('intl')) {
	$base['components']['formatter'] = [
		'class' => 'yii\\base\\Formatter'
	];
} else {
	$base['components']['formatter'] = [
		'class' => 'yii\\i18n\\Formatter',
		'dateFormat' => 'MM/dd/yyyy'
	];
}
return $base;
?>