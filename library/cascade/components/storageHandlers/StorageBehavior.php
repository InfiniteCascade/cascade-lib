<?php
namespace cascade\components\storageHandlers;

use Yii;

use yii\base\Event;
use yii\web\UploadedFile;

class StorageBehavior extends \infinite\db\behaviors\ActiveRecord {
	public $storageEngineClass = 'cascade\\models\\StorageEngine';
    public $storageClass = 'cascade\\models\\Storage';
    public $registryClass = 'cascade\\models\\Registry';
	public $storageAttribute = 'storage_id';

	protected $_storageEngine;

	public function __toString()
    {
        return $this->primaryKey;
    }

	public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            \infinite\db\ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    public function safeAttributes()
	{
		return ['storageEngine'];
	}

    public function loadPostFile($tabId = '')
    {
        $attribute = $this->storageAttribute;
        if (isset($tabId)) {
            $attribute = "[{$tabId}]$attribute";
        }
        if (($fileField = UploadedFile::getInstance($this->owner, $attribute)) && !empty($fileField)) {
            $this->owner->{$this->storageAttribute} = $fileField;
        }
    }

    public function beforeSave($event)
    {
        if (!$this->storageEngine->storageHandler->object->beforeSave($this->storageEngine, $this->owner, $this->storageAttribute)) {
            $event->isValid = false;
            $this->owner->addError($this->storageAttribute, 'Unable to save file in storage engine. Try again later.');
            return false;
        }
    }


    public function afterDelete($event)
    {
        $storageObject = $this->storageObject;
        if (is_null($this->storageEngine)) {
            $this->storageEngine = $this->storageObject->storageEngine;
        }
        if (!$this->storageEngine->storageHandler->object->afterDelete($this->storageEngine, $storageObject)) {
            $event->isValid = false;
            return false;
        }
    }

    public function getStorageObject()
    {
        $registryClass = $this->registryClass;
        return $registryClass::getObject($this->owner->{$this->storageAttribute});
    }


    public function beforeValidate($event)
    {
    	if (empty($this->storageEngine)) {
    		$this->owner->addError($this->storageAttribute, 'Unknown storage engine!');
    		return false;
    	} elseif (!$this->storageEngine->storageHandler->object->validate($this->storageEngine, $this->owner, $this->storageAttribute)) {
    		return false;
    	}
    	return true;
    }

	public function getStorageEngine()
	{
		return $this->_storageEngine;
	}

	public function setStorageEngine($value)
	{
        if (is_object($value)) {
            $this->_storageEngine = $value;
        } else {
    		$storageEngineClass = $this->storageEngineClass;
    		$engineTest = $storageEngineClass::findPk($value);
    		if ($engineTest) {
    			return $this->_storageEngine = $engineTest;
    		}
        }
		return false;
	}
}
?>