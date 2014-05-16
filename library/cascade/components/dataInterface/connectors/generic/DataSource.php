<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

use Yii;

use cascade\models\Relation;
use cascade\models\KeyTranslation;

use infinite\helpers\ArrayHelper;
use cascade\components\dataInterface\connectors\generic\Model as GenericModel;

/**
 * DataSource [@doctodo write class description for DataSource]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataSource extends \cascade\components\dataInterface\DataSource
{
    /**
     * @inheritdoc
     */
    public $fieldMapClass = 'cascade\\components\\dataInterface\\connectors\\generic\\FieldMap';
    /**
     * @inheritdoc
     */
    public $dataItemClass = 'cascade\\components\\dataInterface\\connectors\\generic\\DataItem';
    /**
     * @inheritdoc
     */
    public $searchClass = 'cascade\\components\\dataInterface\\connectors\\generic\\Search';

    public $keys = [];

    public $foreignParentKeys = [];
    public $foreignChildKeys = [];

    /**
    * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_LOAD_FOREIGN_DATA_ITEMS, [$this, 'loadForeignDataItems']);
        $this->on(self::EVENT_LOAD_LOCAL_DATA_ITEMS, [$this, 'loadLocalDataItems']);

        return parent::init();
    }

    /**
     * Get foreign data item
     * @param __param_key_type__                 $key __param_key_description__
     * @return __return_getForeignDataItem_type__ __return_getForeignDataItem_description__
     */
    public function getForeignDataItem($key)
    {
        if (!isset($this->_foreignDataItems[$key])) {
            $this->createForeignDataItem(null, ['foreignPrimaryKey' => $key]);
        }
        if (isset($this->_foreignDataItems[$key])) {
            return $this->_foreignDataItems[$key];
        }

        return false;
    }

    /**
     * Get foreign data model
     * @param __param_key_type__                  $key __param_key_description__
     * @return __return_getForeignDataModel_type__ __return_getForeignDataModel_description__
     */
    abstract public function getForeignDataModel($key);


    /**
     * __method_updateLocalObject_description__
     * @param __param_relatedType_type__        $relatedType       __param_relatedType_description__
     * @param __param_foreignPrimaryKey_type__  $foreignPrimaryKey __param_foreignPrimaryKey_description__
     * @param __param_valueMap_type__           $valueMap          __param_valueMap_description__
     * @param __param_fieldMap_type__           $fieldMap          __param_fieldMap_description__
     * @param __param_localModel_type__         $localModel        __param_localModel_description__
     * @return __return_updateLocalObject_type__ __return_updateLocalObject_description__
     */
    public function updateLocalObject($relatedType, $valueMap, $fieldMap, $localModel)
    {
        $localModelClass = $relatedType->primaryModel;
        // @todo eventually we'll probably take some keys out of this
        $searchMap = $valueMap;
        if (isset($searchMap[0])) {
            throw new \Exception("boo");
        }
        if (isset($fieldMap->searchFields) && is_array($fieldMap->searchFields)) {
            foreach ($searchMap as $field => $value) {
                if (!in_array($field, $fieldMap->searchFields)) {
                    unset($searchMap[$field]);
                }
            }
        }
        $fieldParts = explode(':', $fieldMap->localField);
        if ($fieldParts[0] === 'child') {
            $currentRelationsFunction = 'child';
        } else {
            $currentRelationsFunction = 'parent';
        }
        // first, lets see if it exists
        $relatedObject = null;
        $currentRelation = false;
        if (!empty($localModel) && !$localModel->isNewRecord) {
            $test = $localModel->{$currentRelationsFunction}($relatedType->primaryModel, [], ['where' => $searchMap, 'disableAccessCheck' => 1]);
            if ($test) {
                $relatedObject = $test;
                $currentRelation = true;
            }
        }

        if (empty($relatedObject)) {
            $relatedClass = $relatedType->primaryModel;
            $relatedObject = new $relatedClass;
        }
        $relatedObject->attributes = $valueMap;
        if ($relatedObject->save()) {
            return $relatedObject;
        } else {
            $errors = $relatedObject->errors;
            foreach ($fieldMap->mute as $mute) {
                unset($errors[$mute]);
            }
            if (!empty($errors)) {
                \d($relatedObject->attributes);
                \d($errors); exit;
            }
            return false;
        }
    }

    /**
     * __method_buildLocalAttributes_description__
     * @param cascade\components\dataInterface\connectors\db\Model $foreignModel __param_foreignModel_description__
     * @param __param_localModel_type__                            $localModel   __param_localModel_description__ [optional]
     * @return __return_buildLocalAttributes_type__                 __return_buildLocalAttributes_description__
     */
    public function buildLocalAttributes(Model $foreignModel, $localModel = null, $fieldsMap = null)
    {
        $a = [];
        if (is_null($fieldsMap)) {
            $fieldsMap = $this->map;
        }
        foreach ($fieldsMap as $localKey => $fieldMap) {
            if ($localKey === $this->localPrimaryKeyName) { continue; }
            $value = $fieldMap->extractValue($this, $foreignModel);
            if ($fieldMap->testIgnore($value)) { continue; }
            $taxonomyId = null;
            if (isset($fieldMap->taxonomy) && isset($fieldMap->taxonomy['taxonomy_type'])) {
                $taxonomyTypeItem = Yii::$app->collectors['taxonomies']->getOne($fieldMap->taxonomy['taxonomy_type']);
                if (($taxonomyType = $taxonomyTypeItem->object) && isset($taxonomyType)) {
                    if (isset($fieldMap->taxonomy['taxonomy_system_id'])
                        && ($taxonomy = $taxonomyTypeItem->getTaxonomy($fieldMap->taxonomy['taxonomy_system_id']))
                        && (isset($taxonomy))
                    ) {
                        $taxonomyId = [$taxonomy->primaryKey];
                    }
                }
            }

            if (strpos($fieldMap->localField, ':') !== false) {
                // we're feeding the relations
                $relationKey = $value;
                $value = false;
                $fields = [];
                if (!empty($localModel)) {
                    $fields = $localModel->getFields();
                }
                if (!empty($relationKey)) {
                    $fieldParts = explode(':', $fieldMap->localField);
                    $checkField = $fieldParts;
                    $checkField[2] = '';
                    $checkField = implode(':', $checkField);
                    if (isset($fields[$checkField]) && !empty($fields[$checkField]->model->taxonomy_id)) {
                        $taxonomyId = $fields[$checkField]->model->taxonomy_id;
                    }

                    if ($fieldParts[0] === 'child') {
                        $relationship = $this->dummyLocalModel->objectTypeItem->getChild($fieldParts[1]);
                        $relatedType = !empty($relationship) ? $relationship->child : false;
                        $currentRelationsFunction = 'child';
                    } else {
                        $relationship = $this->dummyLocalModel->objectTypeItem->getParent($fieldParts[1]);
                        $relatedType = !empty($relationship) ? $relationship->parent : false;
                        $currentRelationsFunction = 'parent';
                    }
                    if (!$relatedType) { continue; }

                    $relatedObject = null;
                    if (!isset($a['relationModels'])) {
                        $a['relationModels'] = [];
                    }
                    $fieldKey = $fieldParts[0] .'_object_id';
                    if (empty($fieldParts[2]) && (!is_array($relationKey) || isset($relationKey[0]))) {
                        if (!is_array($relationKey)) {
                            $relationKey = [$relationKey];
                        }
                        foreach ($relationKey as $subkey) {
                            // we're just matching to an existing object's primary key
                            if (($relatedObject = $this->module->getLocalObject($relatedType->primaryModel, $subkey)) && is_object($relatedObject)) {
                                $relation = [$fieldKey => $relatedObject->primaryKey];
                                if (isset($taxonomyId)) {
                                    $relation['taxonomy_id'] = $taxonomyId;
                                    $taxonomyId = null;
                                }
                                $a['relationModels'][] = $relation;
                            }
                        }
                    } else {
                        $valueMap = [];
                        // we're creating or updating an existing related object's field
                        if (is_array($relationKey)) {
                            // the localRelatedField is a dummy; build/search for object using this hash
                            $valueMap = $relationKey;
                        } elseif (isset($fieldParts[2])) {
                            $localRelatedField = $fieldParts[2];
                            $valueMap = [$localRelatedField => $relationKey];
                        }
                        if (($relatedObject = $this->updateLocalObject($relatedType, $valueMap, $fieldMap, $localModel)) && is_object($relatedObject)) {
                            $relation = [$fieldKey => $relatedObject->primaryKey];
                            if (isset($taxonomyId)) {
                                $relation['taxonomy_id'] = $taxonomyId;
                                $taxonomyId = null;
                            }
                            $a['relationModels'][] = $relation;
                        }
                    }
                }
            } elseif (!empty($fieldMap->foreignModel)) {
                $relationKey = $value;
                $value = false;
                if (!empty($relationKey)) {
                    // we're filling a local related _id field with another foreign object
                    if (($relatedObject = $this->module->getForeignObject($fieldMap->foreignModel, $relationKey)) && is_object($relatedObject)) {
                        $value = $relatedObject->primaryKey;
                    }
                }
            }
            if ($value !== false) {
                $a[$fieldMap->localField] = $value;
            }
        }

        return $a;
    }

    /**
     * Get unmapped keys
     * @return __return_getUnmappedKeys_type__ __return_getUnmappedKeys_description__
     */
    public function getUnmappedKeys()
    {
        $u = [];
        $f = $this->unmappedForeignKeys;
        $l = $this->unmappedLocalKeys;
        if (!empty($f)) { $u['foreign'] = $f; }
        if (!empty($l)) { $u['local'] = $l; }

        return $u;
    }

    /**
     * Get unmapped local keys
     * @return __return_getUnmappedLocalKeys_type__ __return_getUnmappedLocalKeys_description__
     */
    public function getUnmappedLocalKeys()
    {
        $u = array_diff(array_keys($this->localModel->getMetaData()->columns), array_keys($this->_map));
        unset($u[$this->localPrimaryKeyName]);

        return $u;
    }

    /**
     * Get unmapped foreign keys
     * @return __return_getUnmappedForeignKeys_type__ __return_getUnmappedForeignKeys_description__
     */
    public function getUnmappedForeignKeys()
    {
        return [];
    }

    /**
     * Get local primary key name
     * @return __return_getLocalPrimaryKeyName_type__ __return_getLocalPrimaryKeyName_description__
     */
    public function getLocalPrimaryKeyName()
    {
        return $this->dummyLocalModel->tableSchema->primaryKey;
    }

    /**
     * Get foreign primary key name
     * @return __return_getForeignPrimaryKeyName_type__ __return_getForeignPrimaryKeyName_description__
     */
    public function getForeignPrimaryKeyName()
    {
        return $this->foreignModel->primaryKey();
    }

    /**
     * __method_isRelationKey_description__
     * @param __param_key_type__            $key __param_key_description__
     * @return __return_isRelationKey_type__ __return_isRelationKey_description__
     */
    public function isRelationKey($key)
    {
        return substr($key, -3) === '_id';
    }

    /**
     * __method_generateKey_description__
     * @param cascade\components\dataInterface\connectors\db\Model $foreignObject __param_foreignObject_description__
     * @return __return_generateKey_type__                          __return_generateKey_description__
     */
    public function generateKey(GenericModel $foreignObject, $keyName, $keyValue)
    {
        if (is_null($this->keyGenerator)) {
            $self = $this;
            $this->keyGenerator = function ($foreignModel, $keyName, $keyValue) use ($self) {
                return [$self->module->systemId, $foreignModel->tableName, $keyName, $keyValue];
            };
        }
        $keyGen = $this->keyGenerator;
        $return = $keyGen($foreignObject, $keyName, $keyValue);

        if (isset($return)) {
            if (!is_array($return)) {
                $return = [$return];
            }
            $return = implode('.', $return);
            return $return;
        }

        return null;
    }
    /**
     * Get key translation
     * @param cascade\components\dataInterface\connectors\db\Model $foreignObject __param_foreignObject_description__
     * @return __return_getKeyTranslation_type__                    __return_getKeyTranslation_description__
     */
    public function getKeyTranslation(Model $foreignObject, $key = null)
    {
        if (isset($key)) {
            return $this->internalGetKeyTranslation($foreignObject, $key);
        }

        foreach ($this->keys as $keyName => $keyField) {
            if (!empty($foreignObject->{$keyField})) {
                $key = $this->generateKey($foreignObject, $keyName, $foreignObject->{$keyField});
                $result = $this->internalGetKeyTranslation($foreignObject, $key);
                if (!empty($result)) {
                    return $result;
                }
            }
        }
        
        return false;
    }

    protected function internalGetKeyTranslation(Model $foreignObject, $key) {
        if ($this->settings['universalKey']) {
            return KeyTranslation::findOne(['key' => $key]);
        } else {
            return KeyTranslation::findOne(['key' => $key, 'data_interface_id' => $this->module->collectorItem->interfaceObject->primaryKey]);
        }
    }

    /**
     * Get reverse key translation
     * @param __param_localObject_type__               $localObject __param_localObject_description__
     * @return __return_getReverseKeyTranslation_type__ __return_getReverseKeyTranslation_description__
     */
    public function getReverseKeyTranslation($localObject)
    {
        $key = is_object($localObject) ? $localObject->primaryKey : $localObject;
        if ($this->settings['universalKey']) {
            return KeyTranslation::findOne(['registry_id' => $key]);
        } else {
            return KeyTranslation::findOne(['registry_id' => $key, 'data_interface_id' => $this->module->collectorItem->interfaceObject->primaryKey]);
        }
    }

    /**
     * __method_saveKeyTranslation_description__
     * @param cascade\components\dataInterface\connectors\db\Model $foreignObject __param_foreignObject_description__
     * @param __param_localObject_type__                           $localObject   __param_localObject_description__
     * @return __return_saveKeyTranslation_type__                   __return_saveKeyTranslation_description__
     */
    public function saveKeyTranslation(Model $foreignObject, $localObject)
    {
        $firstKey = null;
        foreach ($this->keys as $keyName => $keyField) {
            if (!empty($foreignObject->{$keyField})) {
                $key = $this->generateKey($foreignObject, $keyName, $foreignObject->{$keyField});
                $keySaved = $this->internalSaveKeyTranslation($foreignObject, $localObject, $key);
                if (!isset($firstKey)) {
                    $firstKey = $keySaved;
                }
            }
        }

        return $firstKey;
    }

    public function internalSaveKeyTranslation(Model $foreignModel, $localObject, $key)
    {
        $keyTranslation = $this->getKeyTranslation($foreignModel, $key);
        if (!$keyTranslation) {
            $keyTranslation = new KeyTranslation;
            $keyTranslation->data_interface_id = $this->module->collectorItem->interfaceObject->primaryKey;
            $keyTranslation->registry_id = $localObject->primaryKey;
            $keyTranslation->key = $key;
            if (!$keyTranslation->save()) {
                \d($keyTranslation->attributes);
                \d($keyTranslation->errors);
                exit;

                return false;
            }
        }
        return $keyTranslation;
    }
    /**
     * __method_loadForeignDataItems_description__
     */
    protected function loadForeignDataItems()
    {
        return true;
    }

    /**
     * __method_createForeignDataItem_description__
     * @param __param_model_type__                  $model  __param_model_description__
     * @param array                                 $config __param_config_description__ [optional]
     * @return __return_createForeignDataItem_type__ __return_createForeignDataItem_description__
     */
    public function createForeignDataItem($model, $config = [])
    {
        $config['isForeign'] = true;
        $config['foreignObject'] = $model;
        if (isset($model)) {
            $model->interface = $this->module;
        }
        $object = $this->createDataItem($config);
        return $this->_foreignDataItems[$object->id] = $object;
    }

    /**
     * __method_createLocalDataItem_description__
     * @param __param_model_type__                $model  __param_model_description__
     * @param array                               $config __param_config_description__ [optional]
     * @return __return_createLocalDataItem_type__ __return_createLocalDataItem_description__
     */
    public function createLocalDataItem($model, $config = [])
    {
        $config['isForeign'] = false;
        $config['localObject'] = $model;

        return $this->createDataItem($config);
    }

    /**
     * __method_createDataItem_description__
     * @param array                          $config __param_config_description__ [optional]
     * @return __return_createDataItem_type__ __return_createDataItem_description__
     */
    protected function createDataItem($config = [])
    {
        if (!isset($config['class'])) {
            $config['class'] = $this->dataItemClass;
        }
        $config['dataSource'] = $this;

        return Yii::createObject($config);
    }

    /**
     * __method_loadLocalDataItems_description__
     */
    protected function loadLocalDataItems()
    {
        $this->_localDataItems = [];
    }

    /**
     * Get module
     * @return __return_getModule_type__ __return_getModule_description__
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }
}
