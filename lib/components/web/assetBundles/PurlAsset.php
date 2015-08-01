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
 * PurlAsset [[@doctodo class_description:cascade\components\web\assetBundles\PurlAsset]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class PurlAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/purl';
    /**
     * @inheritdoc
     */
    public $css = [];
    /**
     * @inheritdoc
     */
    public $js = [
        'purl.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->publishOptions['beforeCopy'] = function ($from, $to) {
            $acceptable = ['purl.js'];

            return in_array(basename($from), $acceptable) || in_array(basename(dirname($from)), $acceptable);
        };
        parent::init();
    }
}
