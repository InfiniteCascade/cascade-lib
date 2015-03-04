<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

use infinite\base\exceptions\Exception;
use infinite\base\FileInterface;
use infinite\web\UploadedFile;
use Yii;
use yii\base\Event;

/**
 * StorageBehavior [@doctodo write class description for StorageBehavior].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class StorageBehavior extends \infinite\db\behaviors\ActiveRecord
{
    /**
     */
    public $storageAttribute = 'storage_id';

    /**
     */
    protected $_storageEngine;
    protected $_oldStorage;

    public $required = true;

    /**
     * Converts object to string.
     */
    public function __toString()
    {
        return $this->primaryKey;
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            \infinite\db\ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeAttributes()
    {
        return ['storageEngine', 'storage'];
    }

    /**
     * Set storage.
     */
    public function setStorage($value)
    {
        $value = $this->storageEngine->storageHandler->object->beforeSetStorage($value);
        if ($value instanceof FileInterface) {
            $this->owner->{$this->storageAttribute} = $value;
        } else {
            throw new Exception("Trying to set storage item that isn't part of the file interface!");
        }
    }

    /**
     * Get storage.
     */
    public function getStorage()
    {
        if (isset($this->owner->{$this->storageAttribute}) && $this->owner->{$this->storageAttribute} instanceof FileInterface) {
            return $this->owner->{$this->storageAttribute};
        }

        return;
    }

    /**
     *
     */
    public function loadPostFile($tabId = null)
    {
        $attribute = $this->storageAttribute;
        if (isset($tabId)) {
            $attribute = "[{$tabId}]$attribute";
        }
        if (($fileField = UploadedFile::getInstance($this->owner, $attribute)) && !empty($fileField)) {
            $this->_oldStorage = $this->owner->{$this->storageAttribute};
            $this->owner->{$this->storageAttribute} = $fileField;
        }
    }

    /**
     *
     */
    public function beforeSave($event)
    {
        if (!$this->required && empty($this->owner->{$this->storageAttribute})) {
            return true;
        }
        if (is_object($this->owner->{$this->storageAttribute}) && !$this->storageEngine->storageHandler->object->beforeSave($this->storageEngine, $this->owner, $this->storageAttribute)) {
            $event->isValid = false;
            $this->owner->addError($this->storageAttribute, 'Unable to save file in storage engine. Try again later. (' . $this->storageEngine->storageHandler->object->error . ')');

            return false;
        }
    }

    public function afterSave($event)
    {
        if (!empty($this->_oldStorage) && $this->_oldStorage !== $this->owner->{$this->storageAttribute}) {
            $storageClass = Yii::$app->classes['Storage'];
            $storageObject = $storageClass::get($this->_oldStorage, false);
            if (!empty($storageObject)) {
                $this->handleDelete($storageObject);
            }
        }
    }

    public function handleDelete($storageObject)
    {
        if (is_null($this->storageEngine)) {
            $this->storageEngine = $this->storageObject->storageEngine;
        }
        if (!$this->storageEngine->storageHandler->object->afterDelete($this->storageEngine, $storageObject)) {
            $event->isValid = false;

            return false;
        }
        $storageObject->delete();
    }

    /**
     *
     */
    public function afterDelete($event)
    {
        $this->handleDelete($this->storageObject);
    }

    /**
     *
     */
    public function serve()
    {
        if (!$this->storageEngine || !$this->storageEngine->storageHandler) {
            return false;
        }
        $storageObject = $this->storageObject;
        if (!$storageObject) {
            return false;
        }
        if (!$this->storageEngine->storageHandler->object->serve($storageObject)) {
            return false;
        }

        return true;
    }

    public function getStoragePath()
    {
        if (!$this->storageEngine || !$this->storageEngine->storageHandler) {
            return false;
        }
        $storageObject = $this->storageObject;
        if (!$storageObject) {
            return false;
        }
        if (!method_exists($this->storageEngine->storageHandler->object, 'getPath')) {
            return false;
        }

        return $this->storageEngine->storageHandler->object->getPath($storageObject);
    }
    /**
     * Get storage object.
     */
    public function getStorageObject()
    {
        if (empty($this->owner->{$this->storageAttribute})) {
            return false;
        }
        $registryClass = Yii::$app->classes['Registry'];

        return $registryClass::getObject($this->owner->{$this->storageAttribute});
    }

    /**
     *
     */
    public function beforeValidate($event)
    {
        if (!$this->required && empty($this->owner->{$this->storageAttribute})) {
            return true;
        }
        if (empty($this->storageEngine)) {
            $this->owner->addError($this->storageAttribute, 'Unknown storage engine!');

            return false;
        } elseif (!$this->storageEngine->storageHandler->object->validate($this->storageEngine, $this->owner, $this->storageAttribute)) {
            return false;
        }

        return true;
    }

    /**
     * Get storage engine.
     */
    public function getStorageEngine()
    {
        if (is_null($this->_storageEngine)) {
            $storageEngineClass = Yii::$app->classes['StorageEngine'];
            $this->storageEngine = $storageEngineClass::find()->setAction('read')->andWhere(['handler' => Yii::$app->params['defaultStorageEngine']])->one();
        }

        return $this->_storageEngine;
    }

    /**
     * Set storage engine.
     */
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
