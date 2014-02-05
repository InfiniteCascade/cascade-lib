<?php
/**
 * ./app/components/objects/RObjectType.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\storageHandlers;

use Yii;
use infinite\base\exceptions\Exception;
use cascade\models\Storage;
use cascade\models\StorageEngine;

class Item extends \infinite\base\collector\Item {
	public $publicEngine = false;
	public $publicEngineGroup = 'top';

	public function init()
	{
		parent::init();
		if ($this->publicEngine !== false && Yii::$app->isDbAvailable) {
			Yii::$app->collectors->onAfterInit([$this, 'ensurePublicEngine']);
		}
	}

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
				$publicEngine->allow('read');
			}
		}
	}
}