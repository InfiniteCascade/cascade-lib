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
        'css/browse.css'
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'js/browse.js'
    ];
    /**
     * @inheritdoc
     */
    public $depends = ['yii\web\JqueryAsset', 'yii\bootstrap\BootstrapAsset'];
}
