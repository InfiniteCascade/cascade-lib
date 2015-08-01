<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\assetBundles;

use yii\web\AssetBundle;

/**
 * AppAsset [[@doctodo class_description:cascade\components\web\assetBundles\AppAsset]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@cascade/assets/app';

    // public $basePath = '@webroot';
    // public $baseUrl = '@web';
    /**
     * @inheritdoc
     */
    public $css = [
        'css/site.css',
        'css/cascade.editInPlace.css',
        'css/cascade.activityFeed.css',
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'js/cascade.core.js',
        'js/cascade.refreshable.js',
        'js/cascade.objectSearch.js',
        'js/cascade.objectBrowse.js',
        'js/cascade.objectSelector.js',
        'js/cascade.canisFilter.js',
        'js/cascade.editInPlace.js', // maybe move this to object? not sure if it will be used outside object view,
        'js/cascade.activityFeed.js',
        'js/cascade.types.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'canis\web\assetBundles\CanisAsset',
        'canis\web\assetBundles\CanisBrowserAsset',
        'canis\web\assetBundles\BootstrapSelectAsset',
        'canis\web\assetBundles\BootstrapDatepickerAsset',
        'canis\web\assetBundles\TimeAgoAsset',
        'canis\web\assetBundles\BootstrapTypeaheadAsset',
        'cascade\components\web\assetBundles\PurlAsset',
        'cascade\components\web\assetBundles\VibeAsset',
    ];
}
