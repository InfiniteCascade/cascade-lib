<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * RelatedObjects [[@doctodo class_description:cascade\components\db\behaviors\RelatedObjects]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelatedObjects extends \infinite\db\behaviors\ActiveRecord
{
    /**
     * @var [[@doctodo var_type:companionObject]] [[@doctodo var_description:companionObject]]
     */
    public $companionObject = false;
    /**
     * @var [[@doctodo var_type:companionRelationship]] [[@doctodo var_description:companionRelationship]]
     */
    public $companionRelationship = false;
    /**
     * @var [[@doctodo var_type:companionRole]] [[@doctodo var_description:companionRole]]
     */
    public $companionRole = false;
    /**
     * @var [[@doctodo var_type:relation]] [[@doctodo var_description:relation]]
     */
    public $relation;
    /**
     * @var [[@doctodo var_type:_relatedObjects]] [[@doctodo var_description:_relatedObjects]]
     */
    protected $_relatedObjects = [];
    /**
     * @var [[@doctodo var_type:_relatedObjectsFlat]] [[@doctodo var_description:_relatedObjectsFlat]]
     */
    protected $_relatedObjectsFlat = [];
    /**
     * @var [[@doctodo var_type:_relations]] [[@doctodo var_description:_relations]]
     */
    protected $_relations = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
        ];
    }
    /**
     * [[@doctodo method_description:collectModels]].
     *
     * @param array $models [[@doctodo param_description:models]] [optional]
     *
     * @return [[@doctodo return_type:collectModels]] [[@doctodo return_description:collectModels]]
     */
    public function collectModels($models = [])
    {
        if (!isset($models['relations'])) {
            $models['relations'] = [];
        }
        if ($this->owner->tabularId) {
            $models[$this->owner->tabularId] = $this->owner;
        } else {
            $models['primary'] = $this->owner;
        }
        foreach ($this->_relatedObjectsFlat as $related) {
            $models = $related->collectModels($models);
        }
        foreach ($this->_relations as $key => $relation) {
            if (!is_object($relation)) {
                if (!isset($relation['class'])) {
                    $relation['class'] = Yii::$app->classes['Relation'];
                }
                $relation = Yii::createObject($relation);
            }
            $models['relations'][$key] = $relation;
        }

        return $models;
    }

    /**
     * [[@doctodo method_description:beforeSave]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]]
     *
     * @return [[@doctodo return_type:beforeSave]] [[@doctodo return_description:beforeSave]]
     */
    public function beforeSave($event)
    {
        foreach ($this->_relations as $key => $relation) {
            unset($relation['_moduleHandler']);
            if (!empty($this->companionObject)) {
                if ($this->companionRole === 'child') {
                    $relation['parent_object_id'] = $this->companionObject->primaryKey;
                } else {
                    $relation['child_object_id'] = $this->companionObject->primaryKey;
                }
            }
            if (empty($relation['child_object_id']) && empty($relation['parent_object_id'])) {
                if (!empty($this->_relations) && (!$this->companionObject || empty($this->companionObject->primaryKey))) {
                    $event->isValid = false;
                    $this->owner->addError('_', 'Saving relations with no companion object! ' . get_class($this->owner));

                    return false;
                }
            }
            $this->owner->registerRelationModel($relation, $key);
        }
    }

    /**
     * [[@doctodo method_description:afterSave]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]]
     *
     * @return [[@doctodo return_type:afterSave]] [[@doctodo return_description:afterSave]]
     */
    public function afterSave($event)
    {
        foreach ($this->_relatedObjectsFlat as $relatedObject) {
            if (!$relatedObject->save()) {
                $event->handled = false;
                $this->owner->addError('_', $relatedObject->objectType->title->upperSingular . ' could not be saved!');
            }
        }

        return $event->handled;
    }

    /**
     * [[@doctodo method_description:beforeValidate]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]]
     *
     * @return [[@doctodo return_type:beforeValidate]] [[@doctodo return_description:beforeValidate]]
     */
    public function beforeValidate($event)
    {
        foreach ($this->_relatedObjectsFlat as $relatedObject) {
            if (!$relatedObject->validate()) {
                $this->owner->addError('_', $relatedObject->objectType->title->upperSingular . ' did not validate.');
                $event->isValid = false;

                return false;
            }
        }

        return true;
    }

    /**
     * Set related objects.
     *
     * @param [[@doctodo param_type:relatedObjects]] $relatedObjects [[@doctodo param_description:relatedObjects]]
     */
    public function setRelatedObjects($relatedObjects)
    {
        foreach ($relatedObjects as $modelName => $objects) {
            if (!isset($this->_relatedObjects[$modelName])) {
                $this->_relatedObjects[$modelName] = [];
            }
            foreach ($objects as $tabId => $objectAttributes) {
                if (!isset($objectAttributes['_moduleHandler'])) {
                    continue;
                }
                list($relationship, $role) = $this->owner->objectType->getRelationship($objectAttributes['_moduleHandler']);
                $relatedHandler = $this->owner->objectType->getRelatedType($objectAttributes['_moduleHandler']);
                if (!$relatedHandler) {
                    continue;
                }
                $objectAttributes = array_merge([
                    'companionObject' => $this->owner,
                    'companionRelationship' => $relationship,
                    'companionRole' => $role,
                    ], $objectAttributes);
                $object = $relatedHandler->getModel(null, $objectAttributes);
                $object->tabularId = $objectAttributes['_moduleHandler'];
                if ((!$object
                    || $object->isEmptyObject())
                    && !($relationship->required)
                ) {
                    continue;
                }

                $object->companionObject = $object->indirectObject = $this->owner;
                $object->companionRelationship = $relationship;
                $object->companionRole = $role;
                $this->_relatedObjects[$modelName][$tabId] = $object;
                $this->_relatedObjectsFlat[] = $object;
            }
        }
    }

    /**
     * Get related objects.
     *
     * @return [[@doctodo return_type:getRelatedObjects]] [[@doctodo return_description:getRelatedObjects]]
     */
    public function getRelatedObjects()
    {
        return $this->_relatedObjects;
    }

    /**
     * Set relations.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setRelations($value)
    {
        if ($this->companionObject) {
            $baseObject = $this->companionObject;
        } else {
            $baseObject = $this->owner;
        }
        $fields = $baseObject->getFields();
        foreach ($value as $tabId => $relation) {
            if (!isset($relation['_moduleHandler'])) {
                \d("boom");
                exit;
                continue;
            }
            if (!isset($fields[$relation['_moduleHandler']])) {
                \d($relation['_moduleHandler']);
                \d(array_keys($fields));
                exit;
                continue;
            }
            $baseAttributes = [];
            $model = $fields[$relation['_moduleHandler']]->model;
            if (empty($model)) {
                $model = $fields[$relation['_moduleHandler']]->resetModel();
            }
            $model->attributes = $relation;
            $model->_moduleHandler = $relation['_moduleHandler'];
            $model->tabularId = $relation['_moduleHandler'];
            list($relationship, $role) = $baseObject->objectType->getRelationship($model->_moduleHandler);
            $relatedHandler = $baseObject->objectType->getRelatedType($model->_moduleHandler);
            if (!$relatedHandler) {
                continue;
            }
            if (!$this->owner->tabularId  // primary object
                && !$this->owner->isNewRecord
                && empty($model->parent_object_id)
                && empty($model->child_object_id)) {
                continue;
            }
            $this->_relations[$tabId] = $model;
        }
    }

    /**
     * Get relations.
     *
     * @return [[@doctodo return_type:getRelations]] [[@doctodo return_description:getRelations]]
     */
    public function getRelations()
    {
        return $this->_relations;
    }

    /**
     * @inheritdoc
     */
    public function safeAttributes()
    {
        return ['relatedObjects', 'relations', 'companionObject', 'companionRole', 'companionRelationship'];
    }
}
