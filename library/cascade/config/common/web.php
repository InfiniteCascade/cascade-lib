<?php
/**
 * ./app/config/environments/common/main.php.
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 */
$config = include __DIR__.DIRECTORY_SEPARATOR.'base.php';
$config['controllerNamespace'] = 'cascade\controllers';
$config['controllerMap'] = [
    // 'admin' => \cascade\controllers\admin\DefaultController::className(),
    // 'admin/interface' => \cascade\controllers\admin\InterfaceController::className(),
];
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
$config['components']['webState'] = [
    'class' => 'infinite\web\State',
];
$config['components']['session'] = [
    'class' => 'yii\redis\Session',
    'timeout' => '4000', // be sure to change yiic.php too
];
$config['components']['urlManager'] = [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'cache' => null, // disable in production
    'rules' => [
        // a standard rule mapping '/' to 'site/index' action
        '' => 'app/index',

        '<action:(login|logout|stream)>' => 'app/<action>',

        '<action:(browse)>/<type:\S+>' => 'object/<action>',
        '<action:(search|browse-hierarchy)>' => 'object/<action>',
        '<action:(view)>:<subaction:\S+>/<id:\S+>' => 'object/<action>',

        '<action:(update|link|set-primary|delete|view|access|photo)>/<id:\S+>' => 'object/<action>',
        '<action:(create)>/<type:\S+>' => 'object/<action>',

        ['class' => 'cascade\components\rest\UrlRule'],

        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    ],
];
$config['components']['assetManager'] = [
    'linkAssets' => false,
];

return $config;
