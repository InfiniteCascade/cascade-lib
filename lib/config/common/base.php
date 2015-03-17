<?php
use cascade\components\base\Bootstrap;
use cascade\components\base\ClassManager;
use cascade\components\security\Gatekeeper;
use cascade\components\web\View;
use canis\base\FileStorage;
use canis\web\Response;
use yii\base\Formatter as BaseFormatter;
use yii\caching\FileCache;
use yii\i18n\Formatter as I18nFormatter;
use yii\log\FileTarget;
use yii\redis\Cache;

defined('TEAL_ROLE_LEVEL_OWNER') || define('TEAL_ROLE_LEVEL_OWNER', 600); // owner levels: 501-600
defined('TEAL_ROLE_LEVEL_MANAGER') || define('TEAL_ROLE_LEVEL_MANAGER', 500); // manager levels: 401-500
defined('TEAL_ROLE_LEVEL_EDITOR') || define('TEAL_ROLE_LEVEL_EDITOR', 400); // editor levels: 301-400
defined('TEAL_ROLE_LEVEL_COMMENTER') || define('TEAL_ROLE_LEVEL_COMMENTER', 300); // commenter levels: 201-300; doesn't exist in system
defined('TEAL_ROLE_LEVEL_VIEWER') || define('TEAL_ROLE_LEVEL_VIEWER', 200); // viewer levels: 101-200
defined('TEAL_ROLE_LEVEL_BROWSER') || define('TEAL_ROLE_LEVEL_BROWSER', 100); // viewer levels: 1-100

$base = [
    'id' => 'cascade',
    'name' => 'Cascade',
    'basePath' => TEAL_APP_PATH,
    'vendorPath' => TEAL_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'vendor',
    'runtimePath' => TEAL_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'runtime',
    'bootstrap' => ['collectors', Bootstrap::className()],
    'language' => 'en',
    'modules' => include(TEAL_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'modules.php'),
    'extensions' => include(TEAL_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'yiisoft' . DIRECTORY_SEPARATOR . 'extensions.php'),

    // application components
    'components' => [
        'classes' => [
            'class' => ClassManager::className(),
        ],
        'fileStorage' => [
            'class' => FileStorage::className(),
        ],
        'db' => include(TEAL_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "database.php"),
        'gk' => [
            'class' => Gatekeeper::className(),
            'authority' => [
                'type' => 'Individual',
            ],
        ],
        'redis' => include(TEAL_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'redis.php'),
        'collectors' => include(TEAL_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . 'collectors.php'),
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
    'params' => include(TEAL_APP_ENVIRONMENT_PATH . DIRECTORY_SEPARATOR . "params.php"),
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
