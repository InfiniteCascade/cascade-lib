<?php
namespace cascade\components\types;

use Yii;

use infinite\base\exceptions\Exception;
use infinite\helpers\ArrayHelper;

class Collector extends \infinite\base\collector\Module
{
    protected $_tableRegistry;

    public function getCollectorItemClass()
    {
        return 'cascade\\components\\types\\Item';
    }

    public function getModulePrefix()
    {
        return 'Type';
    }

    public function isReady()
    {
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

    public function initialize()
    {
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

    public function registerObjectType($module)
    {
        if (!Yii::$app->isDbAvailable) { return false; }
        $systemId = $module->systemId;

        if (!isset($this->tableRegistry[$systemId])) {
            $objectTypeClass = Yii::$app->classes['ObjectTypeRegistry'];
            $this->_tableRegistry[$systemId] = new $objectTypeClass;
            $this->_tableRegistry[$systemId]->name = $systemId;
            $this->_tableRegistry[$systemId]->system_version = $module->version;
            if (!$this->_tableRegistry[$systemId]->save()) {
                unset($this->_tableRegistry[$systemId]);

                return false;
            }
            $module->objectTypeModel = $this->_tableRegistry[$systemId];
            if (!$module->setup()) {
                $this->_tableRegistry[$systemId]->delete();
                unset($this->_tableRegistry[$systemId]);

                return false;
            }
        }

        if (isset($this->_tableRegistry[$systemId])) {
            $module->objectTypeModel = $this->_tableRegistry[$systemId];
        }

        return true;
    }

    public function getTableRegistry()
    {
        if (is_null($this->_tableRegistry)) {
            $objectTypeClass = Yii::$app->classes['ObjectTypeRegistry'];
            $this->_tableRegistry = [];
            if ($objectTypeClass::tableExists()) {
                $om = $objectTypeClass::find()->all();
                $this->_tableRegistry = ArrayHelper::index($om, 'name');
            }
        }

        return $this->_tableRegistry;
    }
    /**
     *
     *
     * @param  unknown $parent
     * @param  unknown $child
     * @param  unknown $options (optional)
     * @return unknown
     */
    public function addRelationship($parent, $child, $options = [])
    {
        $parentRef = $this->getOne($parent);
        $childRef = $this->getOne($child);
        $relationship = Relationship::getOne($parentRef, $childRef, $options);
        $parentRef->addChild($child, $relationship);
        $childRef->addParent($parent, $relationship);

        return true;
    }

    public function getAuthorities()
    {
        $authorities = [];
        foreach ($this->getAll() as $typeItem) {
            if (isset($typeItem->object) && $typeItem->object->getBehavior('Authority') !== null) {
                $authorities[$typeItem->object->systemId] = $typeItem->object;
            }
        }

        return $authorities;
    }

}
