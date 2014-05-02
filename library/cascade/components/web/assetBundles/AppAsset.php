<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\assetBundles;

use yii\web\AssetBundle;

/**
 * AppAsset [@doctodo write class description for AppAsset]
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
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
        'css/cascade.editInPlace.css'
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
        'js/cascade.infiniteFilter.js',
        'js/cascade.editInPlace.js' // maybe move this to object? not sure if it will be used outside object view
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'infinite\\web\\assetBundles\\InfiniteAsset',
        'infinite\\web\\assetBundles\\InfiniteBrowserAsset',
        'infinite\\web\\assetBundles\\BootstrapSelectAsset',
        'infinite\\web\\assetBundles\\BootstrapDatepickerAsset',
        'cascade\\components\\web\\assetBundles\\BootstrapTypeaheadAsset',
        'cascade\\components\\web\\assetBundles\\SocketIoAsset'
    ];
}
