<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cascade\components\web\assetBundles;

use yii\web\AssetBundle;

class BootstrapTypeaheadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/twitter/typeahead.js/dist';
	public $css = [];
	public $js = [
		'typeahead.min.js'
	];
    public $depends = ['yii\web\JqueryAsset', 'yii\bootstrap\BootstrapAsset'];
}
