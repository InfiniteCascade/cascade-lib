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
 * QueryBuilderAsset [[@doctodo class_description:cascade\components\web\assetBundles\QueryBuilderAsset]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class QueryBuilderAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/bower/jQuery-QueryBuilder/dist';
    /**
     * @inheritdoc
     */
    public $css = [
        'css/query-builder.default.min.css',
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'js/query-builder.standalone.min.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = ['yii\web\JqueryAsset'];
}
