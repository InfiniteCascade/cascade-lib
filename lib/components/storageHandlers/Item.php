<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\storageHandlers;

use cascade\models\Storage;
use cascade\models\StorageEngine;
use teal\base\exceptions\Exception;
use Yii;

/**
 * Item [[@doctodo class_description:cascade\components\storageHandlers\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \teal\base\collector\Item
{
    /**
     * @var [[@doctodo var_type:publicEngine]] [[@doctodo var_description:publicEngine]]
     */
    public $publicEngine = false;
    /**
     * @var [[@doctodo var_type:publicEngineGroup]] [[@doctodo var_description:publicEngineGroup]]
     */
    public $publicEngineGroup = 'top';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->publicEngine !== false && Yii::$app->isDbAvailable) {
            Yii::$app->collectors->onAfterInit([$this, 'ensurePublicEngine']);
        }
    }

    /**
     * [[@doctodo method_description:ensurePublicEngine]].
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     */
    public function ensurePublicEngine()
    {
        if ($this->publicEngine !== false) {
            // @todo cache this
            $publicEngine = StorageEngine::find()->disableAccessCheck()->where(['handler' => $this->systemId])->one();
            if (empty($publicEngine)) {
                $publicEngine = new StorageEngine();
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
