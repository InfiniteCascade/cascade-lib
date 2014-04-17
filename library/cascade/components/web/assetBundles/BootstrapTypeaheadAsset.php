<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\assetBundles;

use yii\web\AssetBundle;

/**
 * BootstrapTypeaheadAsset [@doctodo write class description for BootstrapTypeaheadAsset]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class BootstrapTypeaheadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/twitter/typeahead.js/dist';
    public $css = [];
    public $js = [
        'typeahead.bundle.min.js'
    ];
    public $depends = ['yii\web\JqueryAsset', 'yii\bootstrap\BootstrapAsset'];
}
