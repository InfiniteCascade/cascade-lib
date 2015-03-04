<?php
use cascade\components\base\Bootstrap;
use cascade\components\base\ClassManager;
use cascade\components\security\Gatekeeper;
use cascade\components\web\View;
use infinite\base\FileStorage;
use infinite\web\Response;
use yii\base\Formatter as BaseFormatter;
use yii\caching\FileCache;
use yii\i18n\Formatter as I18nFormatter;
use yii\log\FileTarget;
use yii\redis\Cache;

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
    'bootstrap' => ['collectors', Bootstrap::className()],
    'language' => 'en',
    'modules' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'modules.php'),
    'extensions' => include(INFINITE_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'yiisoft' . DIRECTORY_SEPARATOR . 'extensions.php'),

    // application components
    'components' => [
        'classes' => [
            'class' => ClassManager::className(),
        ],
        'fileStorage' => [
            'class' => FileStorage::className(),
        ],
        'db' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "database.php"),
        'gk' => [
            'class' => Gatekeeper::className(),
            'authority' => [
                'type' => 'Individual',
            ],
        ],
        'redis' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'redis.php'),
        'collectors' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'collectors.php'),
        'cache' => ['class' => Cache::className()],
        'fileCache' => ['class' => FileCache::className()],
        'errorHandler' => [
            'discardExistingOutput' => false,
        ],
        'view' => [
            'class' => View::className(),
        ],
        'response' => [
            'class' => Response::className(),
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::className(),
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => include(INFINITE_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "params.php"),
];
if (!extension_loaded('intl')) {
    $base['components']['formatter'] = [
        'class' => BaseFormatter::className(),
    ];
} else {
    $base['components']['formatter'] = [
        'class' => I18nFormatter::className(),
        'dateFormat' => 'MM/dd/yyyy',
    ];
}

return $base;
