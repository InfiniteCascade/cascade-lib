<?php
namespace cascade\components\types;

use Yii;

use infinite\base\exceptions\Exception;
use infinite\helpers\ArrayHelper;

class Collector extends \infinite\base\collector\Module
{
	public $objectTypeRegistryClass = 'cascade\models\ObjectType';

	protected $_tableRegistry;

	public function getCollectorItemClass() {
		return 'cascade\components\types\Item';
	}


	public function getModulePrefix() {
		return 'Type';
	}

	public function isReady() {
		$this->load();
        foreach ($this->bucket as $type) {
	        if (!$type->object) { continue; }
	        if (!isset($this->tableRegistry[$type->object->systemId])) {
	        	Yii::trace("Type {$type->object->systemId} is not registered in the object type registry.");
            	return false;
	        }
	        if ($this->tableRegistry[$type->object->systemId]->system_version < $type->object->version) {
	        	Yii::trace("Type {$type->object->systemId} is out of date.");
            	return false;
	        }
	        Yii::trace("Type {$type->object->systemId} ready to go!");
        }
        return true;
	}

	public function initialize() {
		foreach ($this->bucket as $type) {
			if (is_null($type->object) || !$type->object) {
				// module isn't in our installation
				continue;
			}
			$module = $type->object;
			$systemId = $module->systemId;
			// Database module registry
			$this->registerObjectType($module);

			if (!isset($this->tableRegistry[$systemId])) {
				throw new Exception("Unable to initialize module $systemId");
			}

			if ($this->tableRegistry[$systemId]->system_version < $module->version) {
				$oldVersion = $this->_tableRegistry[$systemId]->system_version;
				$this->_tableRegistry[$systemId]->system_version = $module->version;
				if (!$module->upgrade($oldVersion) || !$this->_tableRegistry[$systemId]->save()) {
					throw new Exception("Unable to upgrade module $systemId to {$module->version} from {$oldVersion}");
				}
			}
		}
		return true;	
	}

	public function registerObjectType($module) {
		$systemId = $module->systemId;

		if (!isset($this->tableRegistry[$systemId])) {
			$objectTypeClass = $this->objectTypeRegistryClass;
			$this->_tableRegistry[$systemId] = new $objectTypeClass;
			$this->_tableRegistry[$systemId]->name = $systemId;
			$this->_tableRegistry[$systemId]->system_version = $module->version;
			if (!$module->setup() || !$this->_tableRegistry[$systemId]->save()) {
				unset($this->_tableRegistry[$systemId]);
			}
		}
		return true;
	}

	public function getTableRegistry() {
		if (is_null($this->_tableRegistry)) {
			Yii::beginProfile(__CLASS__.'::'.__FUNCTION__);
			$objectTypeClass = $this->objectTypeRegistryClass;
			$this->_tableRegistry = [];
			
			Yii::beginProfile(__CLASS__.'::'.__FUNCTION__ .'::tableExists');
			if ($objectTypeClass::tableExists()) {
				Yii::endProfile(__CLASS__.'::'.__FUNCTION__ .'::tableExists');

				Yii::beginProfile(__CLASS__.'::'.__FUNCTION__ .'::query');
				$om = $objectTypeClass::find()->all();
				Yii::endProfile(__CLASS__.'::'.__FUNCTION__ .'::query');

				Yii::beginProfile(__CLASS__.'::'.__FUNCTION__ .'::index');
				$this->_tableRegistry = ArrayHelper::index($om, 'name');
				Yii::endProfile(__CLASS__.'::'.__FUNCTION__ .'::index');
			} else {
				Yii::endProfile(__CLASS__.'::'.__FUNCTION__ .'::tableExists');
			}
			Yii::endProfile(__CLASS__.'::'.__FUNCTION__);
		}
		return $this->_tableRegistry;
	}
	/**
	 *
	 *
	 * @param unknown $parent
	 * @param unknown $child
	 * @param unknown $options (optional)
	 * @return unknown
	 */
	public function addRelationship($parent, $child, $options = array()) {
		$parentRef = $this->getOne($parent);
		$childRef = $this->getOne($child);
		$relationship = Relationship::getOne($parentRef, $childRef, $options);
		$parentRef->addChild($child, $relationship);
		$childRef->addParent($parent, $relationship);
		return true;
	}

}
?>