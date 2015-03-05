<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\generic;

use cascade\components\dataInterface\connectors\generic\Model as GenericModel;
use cascade\models\KeyTranslation;
use cascade\models\Relation;
use Yii;

/**
 * DataSource [[@doctodo class_description:cascade\components\dataInterface\connectors\generic\DataSource]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class DataSource extends \cascade\components\dataInterface\DataSource
{
    /**
     * @inheritdoc
     */
    public $fieldMapClass = 'cascade\components\dataInterface\connectors\generic\FieldMap';
    /**
     * @inheritdoc
     */
    public $dataItemClass = 'cascade\components\dataInterface\connectors\generic\DataItem';
    /**
     * @inheritdoc
     */
    public $searchClass = 'cascade\components\dataInterface\connectors\generic\Search';

    /**
     * @var [[@doctodo var_type:keys]] [[@doctodo var_description:keys]]
     */
    public $keys = [];

    /**
     * @var [[@doctodo var_type:foreignParentKeys]] [[@doctodo var_description:foreignParentKeys]]
     */
    public $foreignParentKeys = [];
    /**
     * @var [[@doctodo var_type:foreignChildKeys]] [[@doctodo var_description:foreignChildKeys]]
     */
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
     * Get foreign data item.
     *
     * @return [[@doctodo return_type:getForeignDataItem]] [[@doctodo return_description:getForeignDataItem]]
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
     * Get foreign data model.
     */
    abstract public function getForeignDataModel($key);

    /**
     * [[@doctodo method_description:updateLocalObject]].
     *
     * @throws \Exception [[@doctodo exception_description:\Exception]]
     * @return [[@doctodo return_type:updateLocalObject]] [[@doctodo return_description:updateLocalObject]]
     *
     */
    public function updateLocalObject($relatedType, $valueMap, $fieldMap, $localModel)
    {
        $localModelClass = $relatedType->primaryModel;
        // @todo eventually we'll probably take some keys out of this
        $searchMap = $valueMap;
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
            $relatedObject = new $relatedClass();
        }
        $relatedObject->auditDataInterface = $this->module->collectorItem->interfaceObject->primaryKey;
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
                \d($errors);
                exit;
            }

            return false;
        }
    }

    /**
     * [[@doctodo method_description:buildLocalAttributes]].
     *
     * @param cascade\components\dataInterface\connectors\generic\Model $foreignModel [[@doctodo param_description:foreignModel]]
     *
     * @return [[@doctodo return_type:buildLocalAttributes]] [[@doctodo return_description:buildLocalAttributes]]
     */
    public function buildLocalAttributes(Model $foreignModel, $localModel = null, $fieldsMap = null)
    {
        $a = [];
        if (is_null($fieldsMap)) {
            $fieldsMap = $this->map;
        }
        foreach ($fieldsMap as $localKey => $fieldMap) {
            if ($localKey === $this->localPrimaryKeyName) {
                continue;
            }
            $value = $fieldMap->extractValue($this, $foreignModel);
            if ($fieldMap->testIgnore($value)) {
                continue;
            }
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
                    if (!$relatedType) {
                        continue;
                    }

                    $relatedObject = null;
                    if (!isset($a['relationModels'])) {
                        $a['relationModels'] = [];
                    }
                    $fieldKey = $fieldParts[0] . '_object_id';
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
     * Get unmapped keys.
     *
     * @return [[@doctodo return_type:getUnmappedKeys]] [[@doctodo return_description:getUnmappedKeys]]
     */
    public function getUnmappedKeys()
    {
        $u = [];
        $f = $this->unmappedForeignKeys;
        $l = $this->unmappedLocalKeys;
        if (!empty($f)) {
            $u['foreign'] = $f;
        }
        if (!empty($l)) {
            $u['local'] = $l;
        }

        return $u;
    }

    /**
     * Get unmapped local keys.
     *
     * @return [[@doctodo return_type:getUnmappedLocalKeys]] [[@doctodo return_description:getUnmappedLocalKeys]]
     */
    public function getUnmappedLocalKeys()
    {
        $u = array_diff(array_keys($this->localModel->getMetaData()->columns), array_keys($this->_map));
        unset($u[$this->localPrimaryKeyName]);

        return $u;
    }

    /**
     * Get unmapped foreign keys.
     *
     * @return [[@doctodo return_type:getUnmappedForeignKeys]] [[@doctodo return_description:getUnmappedForeignKeys]]
     */
    public function getUnmappedForeignKeys()
    {
        return [];
    }

    /**
     * Get local primary key name.
     *
     * @return [[@doctodo return_type:getLocalPrimaryKeyName]] [[@doctodo return_description:getLocalPrimaryKeyName]]
     */
    public function getLocalPrimaryKeyName()
    {
        return $this->dummyLocalModel->tableSchema->primaryKey;
    }

    /**
     * Get foreign primary key name.
     *
     * @return [[@doctodo return_type:getForeignPrimaryKeyName]] [[@doctodo return_description:getForeignPrimaryKeyName]]
     */
    public function getForeignPrimaryKeyName()
    {
        return $this->foreignModel->primaryKey();
    }

    /**
     * [[@doctodo method_description:isRelationKey]].
     *
     * @return [[@doctodo return_type:isRelationKey]] [[@doctodo return_description:isRelationKey]]
     */
    public function isRelationKey($key)
    {
        return substr($key, -3) === '_id';
    }

    /**
     * [[@doctodo method_description:generateKey]].
     *
     * @param cascade\components\dataInterface\connectors\generic\Model $foreignObject [[@doctodo param_description:foreignObject]]
     *
     * @return [[@doctodo return_type:generateKey]] [[@doctodo return_description:generateKey]]
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

        return;
    }
    /**
     * Get key translation.
     *
     * @param cascade\components\dataInterface\connectors\generic\Model $foreignObject [[@doctodo param_description:foreignObject]]
     *
     * @return [[@doctodo return_type:getKeyTranslation]] [[@doctodo return_description:getKeyTranslation]]
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

    /**
     * [[@doctodo method_description:internalGetKeyTranslation]].
     *
     * @param cascade\components\dataInterface\connectors\generic\Model $foreignObject [[@doctodo param_description:foreignObject]]
     *
     * @return [[@doctodo return_type:internalGetKeyTranslation]] [[@doctodo return_description:internalGetKeyTranslation]]
     */
    protected function internalGetKeyTranslation(Model $foreignObject, $key)
    {
        if ($this->settings['universalKey']) {
            return KeyTranslation::findOne(['key' => $key]);
        } else {
            return KeyTranslation::findOne(['key' => $key, 'data_interface_id' => $this->module->collectorItem->interfaceObject->primaryKey]);
        }
    }

    /**
     * Get reverse key translation.
     *
     * @return [[@doctodo return_type:getReverseKeyTranslation]] [[@doctodo return_description:getReverseKeyTranslation]]
     */
    public function getReverseKeyTranslation($localObject)
    {
        $key = is_object($localObject) ? $localObject->primaryKey : $localObject;
        if ($this->settings['universalKey']) {
            //return KeyTranslation::findOne(['registry_id' => $key]);
            return KeyTranslation::find()->where(['registry_id' => $key])->one();
        } else {
            //return KeyTranslation::findOne(['registry_id' => $key, 'data_interface_id' => $this->module->collectorItem->interfaceObject->primaryKey]);
            return KeyTranslation::find()->where(['registry_id' => $key, 'data_interface_id' => $this->module->collectorItem->interfaceObject->primaryKey])->one();
        }
    }

    /**
     * [[@doctodo method_description:saveKeyTranslation]].
     *
     * @param cascade\components\dataInterface\connectors\generic\Model $foreignObject [[@doctodo param_description:foreignObject]]
     *
     * @return [[@doctodo return_type:saveKeyTranslation]] [[@doctodo return_description:saveKeyTranslation]]
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

    /**
     * [[@doctodo method_description:internalSaveKeyTranslation]].
     *
     * @param cascade\components\dataInterface\connectors\generic\Model $foreignModel [[@doctodo param_description:foreignModel]]
     *
     * @return [[@doctodo return_type:internalSaveKeyTranslation]] [[@doctodo return_description:internalSaveKeyTranslation]]
     */
    public function internalSaveKeyTranslation(Model $foreignModel, $localObject, $key)
    {
        $keyTranslation = $this->getKeyTranslation($foreignModel, $key);
        if (!$keyTranslation) {
            $keyTranslation = new KeyTranslation();
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
     * [[@doctodo method_description:loadForeignDataItems]].
     *
     * @return [[@doctodo return_type:loadForeignDataItems]] [[@doctodo return_description:loadForeignDataItems]]
     */
    protected function loadForeignDataItems()
    {
        return true;
    }

    /**
     * [[@doctodo method_description:createForeignDataItem]].
     *
     * @param array $config [[@doctodo param_description:config]] [optional]
     *
     * @return [[@doctodo return_type:createForeignDataItem]] [[@doctodo return_description:createForeignDataItem]]
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
     * [[@doctodo method_description:createLocalDataItem]].
     *
     * @param array $config [[@doctodo param_description:config]] [optional]
     *
     * @return [[@doctodo return_type:createLocalDataItem]] [[@doctodo return_description:createLocalDataItem]]
     */
    public function createLocalDataItem($model, $config = [])
    {
        $config['isForeign'] = false;
        $config['localObject'] = $model;

        return $this->createDataItem($config);
    }

    /**
     * [[@doctodo method_description:createDataItem]].
     *
     * @param array $config [[@doctodo param_description:config]] [optional]
     *
     * @return [[@doctodo return_type:createDataItem]] [[@doctodo return_description:createDataItem]]
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
     * [[@doctodo method_description:loadLocalDataItems]].
     */
    protected function loadLocalDataItems()
    {
        $this->_localDataItems = [];
    }

    /**
     * Get module.
     *
     * @return [[@doctodo return_type:getModule]] [[@doctodo return_description:getModule]]
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }
}
