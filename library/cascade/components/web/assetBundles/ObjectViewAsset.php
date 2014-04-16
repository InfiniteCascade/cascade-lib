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
class ObjectViewAsset extends AssetBundle
{
    public $sourcePath = '@cascade/assets/object/view';
    public $css = [];
    public $js = [
        'js/cascade.object.view.js',
        'js/cascade.object.access.js',
    ];
    public $depends = [
        'cascade\\components\\web\\assetBundles\\AppAsset'
    ];
}
