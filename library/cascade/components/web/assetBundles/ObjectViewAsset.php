<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\assetBundles;

use yii\web\AssetBundle;

/**
 * ObjectViewAsset [@doctodo write class description for ObjectViewAsset].
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
