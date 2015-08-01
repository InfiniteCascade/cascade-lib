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
 * ObjectViewAsset [[@doctodo class_description:cascade\components\web\assetBundles\ObjectViewAsset]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class ObjectViewAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@cascade/assets/object/view';
    /**
     * @inheritdoc
     */
    public $css = [
        'css/relationship.css',
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'js/cascade.object.view.js',
        'js/cascade.object.access.js',
        'js/cascade.object.relationship.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'cascade\components\web\assetBundles\AppAsset',
    ];
}
