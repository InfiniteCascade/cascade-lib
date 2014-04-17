<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

use Yii;
use infinite\base\exceptions\Exception;
use cascade\models\Storage;
use cascade\models\StorageEngine;

/**
 * Item [@doctodo write class description for Item]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Item extends \infinite\base\collector\Item
{
    /**
     * @var __var_publicEngine_type__ __var_publicEngine_description__
     */
    public $publicEngine = false;
    /**
     * @var __var_publicEngineGroup_type__ __var_publicEngineGroup_description__
     */
    /**
     * @var __var_publicEngine_type__ __var_publicEngine_description__
     */
    /**
     * @var __var_publicEngine_type__ __var_publicEngine_description__
     */
    /**
     * @var __var_publicEngine_type__ __var_publicEngine_description__
     */
    /**
     * @var __var_publicEngine_type__ __var_publicEngine_description__
     */
    /**
     * @var __var_publicEngine_type__ __var_publicEngine_description__
     */
    public $publicEngineGroup = 'top';

    /**
    * @inheritdoc
    **/
    public function init()
    {
        parent::init();
        if ($this->publicEngine !== false && Yii::$app->isDbAvailable) {
            Yii::$app->collectors->onAfterInit([$this, 'ensurePublicEngine']);
        }
    }

    /**
     * __method_ensurePublicEngine_description__
     * @throws Exception __exception_Exception_description__
     */
    public function ensurePublicEngine()
    {
        if ($this->publicEngine !== false) {
            // @todo cache this
            $publicEngine = StorageEngine::find()->disableAccessCheck()->where(['handler' => $this->systemId])->one();
            if (empty($publicEngine)) {
                $publicEngine = new StorageEngine;
                $publicEngine->asGroup($this->publicEngineGroup);
                if (is_array($this->publicEngine)) {
                    $publicEngine->data = serialize($this->publicEngine);
                }
                $publicEngine->handler = $this->systemId;
                if (!$publicEngine->save()) {
                    throw new Exception("Unable to initialize public storage engine for {$this->systemId}");
                }
            }
            if (!$publicEngine->asGroup($this->publicEngineGroup)->can('read')) {
                $publicEngine->allow(['list', 'read']);
            }
        }
    }
}
