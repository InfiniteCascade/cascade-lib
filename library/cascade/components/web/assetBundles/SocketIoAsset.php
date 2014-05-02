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
class SocketIoAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/LearnBoost/socket.io-client';
    /**
     * @inheritdoc
     */
    public $css = [];
    /**
     * @inheritdoc
     */
    public $js = [
        'socket.io.js'
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
            $acceptable = ['socket.io.js'];

            return in_array(basename($from), $acceptable) || in_array(basename(dirname($from)), $acceptable);
        };
        parent::init();
    }
}
