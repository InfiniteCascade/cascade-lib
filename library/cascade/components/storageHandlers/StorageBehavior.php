<?php
namespace cascade\components\storageHandlers;

use Yii;

use yii\base\Event;

class StorageBehavior extends \infinite\db\behaviors\ActiveRecord {
	public $storageEngineClass = 'cascade\\models\\StorageEngine';
	public $storageClass = 'cascade\\models\\Storage';
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
        ];
    }

    public function safeAttributes()
	{
		return ['storageEngine'];
	}

    public function beforeSave($event)
    {

    }

    public function beforeValidate($event)
    {
    	if (empty($this->storageEngine)) {
    		$this->owner->addError($this->storageAttribute, 'Unknown storage engine!');
    		return false;
    	} elseif (!$this->storageEngine->storageHandler->object->validate($this->owner, $this->storageAttribute)) {
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
		$storageEngineClass = $this->storageEngineClass;
		$engineTest = $storageEngineClass::find();
		$engineTest = $engineTest->andWhere([$engineTest->primaryAlias .'.'. $engineTest->primaryTablePk => $value])->one();
		if ($engineTest) {
			return $this->_storageEngine = $engineTest;
		}
		return false;
	}
}
?>