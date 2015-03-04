<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use infinite\base\exceptions\Exception;
use infinite\helpers\ArrayHelper;
use Yii;

/**
 * Collector [@doctodo write class description for Collector].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \infinite\base\collector\Module
{
    /**
     * @var __var__tableRegistry_type__ __var__tableRegistry_description__
     */
    protected $_tableRegistry;

    /**
     * @inheritdoc
     */
    public function getCollectorItemClass()
    {
        return Item::className();
    }

    /**
     * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Type';
    }

    /**
     * @inheritdoc
     */
    public function isReady()
    {
        $this->load();
        foreach ($this->bucket as $type) {
            if (!$type->object) {
                continue;
            }
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

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        Yii::beginProfile('Component:::types::initialize');
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
        Yii::endProfile('Component:::types::initialize');

        return true;
    }

    /**
     * __method_registerObjectType_description__.
     *
     * @param __param_module_type__ $module __param_module_description__
     *
     * @return __return_registerObjectType_type__ __return_registerObjectType_description__
     */
    public function registerObjectType($module)
    {
        if (!Yii::$app->isDbAvailable) {
            return false;
        }
        $systemId = $module->systemId;

        if (!isset($this->tableRegistry[$systemId])) {
            $objectTypeClass = Yii::$app->classes['ObjectTypeRegistry'];
            $this->_tableRegistry[$systemId] = new $objectTypeClass();
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

    /**
     * Get table registry.
     *
     * @return __return_getTableRegistry_type__ __return_getTableRegistry_description__
     */
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
     * __method_addRelationship_description__.
     *
     * @param unknown $parent
     * @param unknown $child
     * @param unknown $options (optional)
     *
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

    /**
     * Get authorities.
     *
     * @return __return_getAuthorities_type__ __return_getAuthorities_description__
     */
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

    public function getPageMeta()
    {
        $m = [];
        foreach ($this->getAll() as $typeItem) {
            if (empty($typeItem->object)) {
                continue;
            }
            $m[$typeItem->systemId] = $typeItem->object->pageMeta;
        }

        return $m;
    }
}
