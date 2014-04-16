<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

use Yii;

use yii\base\Event;
use infinite\web\UploadedFile;
use infinite\base\FileInterface;
use infinite\base\exceptions\Exception;

class StorageBehavior extends \infinite\db\behaviors\ActiveRecord
{
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
        return ['storageEngine', 'storage'];
    }

    public function setStorage($value)
    {
        $value = $this->storageEngine->storageHandler->object->beforeSetStorage($value);
        if ($value instanceof FileInterface) {
            $this->owner->{$this->storageAttribute} = $value;
        } else {
            throw new Exception("Trying to set storage item that isn't part of the file interface!");
        }
    }

    public function getStorage()
    {
        if (isset($this->owner->{$this->storageAttribute}) && $this->owner->{$this->storageAttribute} instanceof FileInterface) {
            return $this->owner->{$this->storageAttribute};
        }

        return null;
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
            $this->owner->addError($this->storageAttribute, 'Unable to save file in storage engine. Try again later. ('.$this->storageEngine->storageHandler->object->error . ')');

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

    public function serve()
    {
        if (!$this->storageEngine || !$this->storageEngine->storageHandler) { return false; }
        $storageObject = $this->storageObject;
        if (!$storageObject) { return false; }
        if (!$this->storageEngine->storageHandler->object->serve($storageObject)) { return false; }

        return true;
    }

    public function getStorageObject()
    {
        $registryClass = Yii::$app->classes['Registry'];

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
        if (is_null($this->_storageEngine)) {
            $storageEngineClass = Yii::$app->classes['StorageEngine'];
            $this->storageEngine = $storageEngineClass::find()->setAction('read')->andWhere(['handler' => Yii::$app->params['defaultStorageEngine']])->one();
        }

        return $this->_storageEngine;
    }

    public function setStorageEngine($value)
    {
        if (is_object($value)) {
            $this->_storageEngine = $value;
        } else {
            $storageEngineClass = Yii::$app->classes['StorageEngine'];
            $engineTest = $storageEngineClass::find()->pk($value)->one();
            if ($engineTest) {
                return $this->_storageEngine = $engineTest;
            }
        }

        return false;
    }
}
