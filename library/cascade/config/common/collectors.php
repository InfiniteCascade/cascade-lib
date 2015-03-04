<?php
$lazyLoad = !(defined('IS_CONSOLE') && IS_CONSOLE);

return [
    'class' => 'infinite\base\collector\Component',
    'cacheTime' => 120,
    'collectors' => [
        'roles' => include(INFINITE_APP_ENVIRONMENT_PATH.DIRECTORY_SEPARATOR.'roles.php'),
        'identityProviders' => include(INFINITE_APP_ENVIRONMENT_PATH.DIRECTORY_SEPARATOR.'identityProviders.php'),
        'types' => [
            'class' => 'cascade\components\types\Collector',
        ],
        'taxonomies' => [
            'class' => 'cascade\components\taxonomy\Collector',
        ],
        'themes' => [
            'class' => 'cascade\components\web\themes\Collector',
        ],
        'widgets' => [
            'class' => 'cascade\components\web\widgets\Collector',
            'lazyLoad' => false,
        ],
        'storageHandlers' => [
            'class' => 'cascade\components\storageHandlers\Collector',
            'initialItems' => [
                'local' => [
                    'object' => [
                        'class' => 'cascade\components\storageHandlers\core\LocalHandler',
                        'bucketFormat' => '{year}.{month}',
                        'baseDir' => INFINITE_APP_INSTALL_PATH.DIRECTORY_SEPARATOR.'storage',
                    ],
                    'publicEngine' => true,
                ],
            ],
        ],
        'sections' => [
            'class' => 'cascade\components\section\Collector',
            'lazyLoad' => $lazyLoad,
        ],
        'dataInterfaces' => [
            'class' => 'cascade\components\dataInterface\Collector',
            'lazyLoad' => $lazyLoad,
        ],
        'tools' => [
            'class' => 'cascade\components\tools\Collector',
            'lazyLoad' => $lazyLoad,
        ],
        'reports' => [
            'class' => 'cascade\components\reports\Collector',
            'lazyLoad' => $lazyLoad,
        ],
    ],
];
