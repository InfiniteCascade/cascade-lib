<?php
/**
 * ./app/config/environments/common/main.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

$config = include(__DIR__ . DIRECTORY_SEPARATOR . 'base.php');
$config['controllerNamespace'] = 'cascade\\controllers';
$config['components']['request'] = [
	'class' => 'cascade\components\web\Request',
	'enableCsrfValidation' => true,
	'enableCookieValidation' => true,
];
$config['components']['user'] = [
	'class' => 'infinite\web\User',
	'enableAutoLogin' => false,
	'identityClass' => 'cascade\models\User',
	'loginUrl' => ['/app/login'],
];
$config['components']['state'] = [
	'class' => 'infinite\web\State'
];
$config['components']['session'] = [
	'class' => 'yii\redis\Session',
	'timeout' => '4000' // be sure to change yiic.php too
];
$config['components']['urlManager'] = [
	'enablePrettyUrl' => true,
	'showScriptName' => false,
	'rules' => [
		// a standard rule mapping '/' to 'site/index' action
		'' => 'app/index',
		
		'<action:(login|logout)>' => 'app/<action>',

		'<action:(search)>' => 'object/<action>',
		'<action:(browse)>/<type:\S+>' => 'object/<action>',
		'<action:(view)>:<subaction:\S+>/<id:\S+>' => 'object/<action>',
		'<action:(view)>/<id:\S+>' => 'object/<action>',
		'<action:(update|delete)>/<id:\S+>' => 'object/<action>',
		'<action:(create)>/<type:\S+>/<object_id:\S+>' => 'object/<action>',
		'tool/<tool:\S+>' => 'object/tool',
		'report/<report:\S+>' => 'object/report',

		// a standard rule to handle 'post/update' and so on
		'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
	]
];
$config['components']['assetManager'] = [
	'linkAssets' => false
];
return $config;