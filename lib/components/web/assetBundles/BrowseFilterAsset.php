<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\assetBundles;

use yii\web\AssetBundle;

/**
 * BrowseFilterAsset [[@doctodo class_description:cascade\components\web\assetBundles\BrowseFilterAsset]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class BrowseFilterAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@cascade/assets/object/browse';
    /**
     * @inheritdoc
     */
    public $css = [
        'css/browse.css',
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'js/browse.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = ['yii\web\JqueryAsset', 'yii\bootstrap\BootstrapAsset'];
}
