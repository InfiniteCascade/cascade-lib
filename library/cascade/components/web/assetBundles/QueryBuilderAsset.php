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
 * BootstrapTypeaheadAsset [@doctodo write class description for BootstrapTypeaheadAsset].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class QueryBuilderAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/mistic100/jquery-querybuilder/src';
    /**
     * @inheritdoc
     */
    public $css = [
        'query-builder.css',
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'query-builder.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = ['yii\web\JqueryAsset'];
}
