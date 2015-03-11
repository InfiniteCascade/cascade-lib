<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
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
        'js/cascade.tealFilter.js',
        'js/cascade.editInPlace.js', // maybe move this to object? not sure if it will be used outside object view,
        'js/cascade.activityFeed.js',
        'js/cascade.types.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'teal\web\assetBundles\TealAsset',
        'teal\web\assetBundles\TealBrowserAsset',
        'teal\web\assetBundles\BootstrapSelectAsset',
        'teal\web\assetBundles\BootstrapDatepickerAsset',
        'teal\web\assetBundles\TimeAgoAsset',
        'teal\web\assetBundles\BootstrapTypeaheadAsset',
        'cascade\components\web\assetBundles\PurlAsset',
        'cascade\components\web\assetBundles\VibeAsset',
    ];
}
