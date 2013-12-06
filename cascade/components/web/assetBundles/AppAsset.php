<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cascade\components\web\assetBundles;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@cascade/assets';

	// public $basePath = '@webroot';
	// public $baseUrl = '@web';
	public $css = ['css/site.css'];
	public $js = ['js/cascade.core.js', 'js/cascade.refreshable.js'];
	public $depends = [
		'infinite\web\assetBundles\InfiniteAsset',
		'infinite\web\assetBundles\BootstrapSelectAsset',
	];
}
