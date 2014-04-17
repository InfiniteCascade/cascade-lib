<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use Yii;

use cascade\components\db\ActiveRecordTrait;

use cascade\components\types\Module as TypeModule;
use cascade\components\types\Relationship;
use cascade\components\types\RelationshipEvent;
use infinite\caching\Cacher;

/**
 * Relation is the model class for table "relation".
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Relation extends \infinite\db\models\Relation
{
    use ActiveRecordTrait;

    /**
    * @inheritdoc
    **/
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Taxonomy' => [
                'class' => 'cascade\\components\\db\\behaviors\\Taxonomy',
                'viaModelClass' => 'RelationTaxonomy',
                'relationKey' => 'relation_id'
            ],
            'PrimaryRelation' => [
                'class' => 'cascade\\components\\db\\behaviors\\PrimaryRelation'
            ]
        ]);
    }

    /**
    * @inheritdoc
    **/
    public function afterSaveRelation($event)
    {
        parent::afterSaveRelation($event);
        if ($this->wasDirty) {
            Cacher::invalidateGroup(['Object', 'relations', $this->parent_object_id]);
            Cacher::invalidateGroup(['Object', 'relations', $this->child_object_id]);
            $parentObject = $this->getParentObject(false);
            $childObject =  $this->getChildObject(false);
            $relationshipEvent = new RelationshipEvent(['parentEvent' => $event, 'parentObject' => $parentObject, 'childObject' => $childObject, 'relationship' => $this->relationship]);
            if ($parentObject) {
                $parentObject->objectType->trigger(TypeModule::EVENT_RELATION_CHANGE, $relationshipEvent);
            }
            if ($childObject) {
                $childObject->objectType->trigger(TypeModule::EVENT_RELATION_CHANGE, $relationshipEvent);
            }
        }
    }

    /**
    * @inheritdoc
    **/
    public function afterDeleteRelation($event)
    {
        parent::afterDeleteRelation($event);
        Cacher::invalidateGroup(['Object', 'relations', $this->parent_object_id]);
        Cacher::invalidateGroup(['Object', 'relations', $this->child_object_id]);

        $parentObject = $this->getParentObject(false);
        $childObject =  $this->getChildObject(false);
        $relationshipEvent = new RelationshipEvent(['parentEvent' => $event, 'parentObject' => $parentObject, 'childObject' => $childObject, 'relationship' => $this->relationship]);
        if ($parentObject) {
            $parentObject->objectType->trigger(TypeModule::EVENT_RELATION_DELETE, $relationshipEvent);
        }
        if ($childObject) {
            $childObject->objectType->trigger(TypeModule::EVENT_RELATION_DELETE, $relationshipEvent);
        }
    }

    /**
     * __method_addFields_description__
     * @param __param_caller_type__       $caller       __param_caller_description__
     * @param __param_fields_type__       $fields       __param_fields_description__
     * @param __param_relationship_type__ $relationship __param_relationship_description__
     * @param __param_owner_type__        $owner        __param_owner_description__
     */
    public function addFields($caller, &$fields, $relationship, $owner)
    {
        $baseField = ['model' => $this];
        if (isset($this->id)) {
            $fields['relation:id'] = $caller->createField('id', $owner, $baseField);
        }
        if (!empty($relationship->taxonomy)
                && ($taxonomyItem = Yii::$app->collectors['taxonomies']->getOne($relationship->taxonomy))
                && ($taxonomy = $taxonomyItem->object)
                && $taxonomy) {
            $fieldName = 'relation:taxonomy_id';
            $fieldSchema = $caller->createColumnSchema('taxonomy_id', ['type' => 'taxonomy', 'phpType' => 'object', 'dbType' => 'taxonomy', 'allowNull' => true]);

            $fields[$fieldName] = $caller->createTaxonomyField($fieldSchema, $taxonomyItem, $owner, $baseField);
        }
    }

    /**
     * Get relationship
     * @return __return_getRelationship_type__ __return_getRelationship_description__
     */
    public function getRelationship()
    {
        if (!isset($this->parentObject) || !isset($this->parentObject->objectTypeItem)) { return false; }
        if (!isset($this->childObject) || !isset($this->childObject->objectTypeItem)) { return false; }

        return Relationship::getOne($this->parentObject->objectTypeItem, $this->childObject->objectTypeItem);
    }
}
