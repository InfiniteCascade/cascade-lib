<?php
$db = [];

return [
    'id' => 'bootstrap-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [],
    'controllerPath' => dirname(__DIR__).'/commands',
    'controllerNamespace' => 'cascade\commands',
    'modules' => [
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => [],
];
