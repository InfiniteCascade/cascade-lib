<?php
return [
	'id' => 'cascade',
	'name' => 'Cascade',
	'basePath' => INFINITE_APP_PATH,
	'vendorPath' => INFINITE_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'vendor',
	'runtimePath' => INFINITE_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'runtime',
	'preload' => ['log', 'collectors'],
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
		'cache' => ['class' => 'yii\redis\Cache'],
		//'cache' => ['class' => 'yii\caching\DummyCache'],
		'errorHandler' => [
			'discardExistingOutput' => false
		],
		'view' => [
			'class' => 'cascade\components\web\View',
		],
		'response' => [
			'class' => 'infinite\web\Response'
		],
		
		'log' => [
			'class' => 'yii\log\Logger',
			'traceLevel' => YII_DEBUG ? 7 : 0,
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
?>