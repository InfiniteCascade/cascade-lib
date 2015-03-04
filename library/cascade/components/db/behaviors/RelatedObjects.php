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
 * Roleable [@doctodo write class description for Roleable].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelatedObjects extends \infinite\db\behaviors\ActiveRecord
{
    public $companionObject = false;
    public $companionRelationship = false;
    public $companionRole = false;
    public $relation;
    protected $_relatedObjects = [];
    protected $_relatedObjectsFlat = [];
    protected $_relations = [];

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

    public function getRelatedObjects()
    {
        return $this->_relatedObjects;
    }

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

    public function getRelations()
    {
        return $this->_relations;
    }

    public function safeAttributes()
    {
        return ['relatedObjects', 'relations', 'companionObject', 'companionRole', 'companionRelationship'];
    }
}
