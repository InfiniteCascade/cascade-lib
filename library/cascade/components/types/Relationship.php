<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use cascade\components\db\behaviors\Relatable;
use infinite\base\exceptions\Exception;
use Yii;

/**
 * Relationship [[@doctodo class_description:cascade\components\types\Relationship]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Relationship extends \infinite\base\Object
{
    const HAS_MANY = 0x01;
    const HAS_ONE = 0x02;
    const ROLE_PARENT = 0x03;
    const ROLE_CHILD = 0x04;

    /**
     * @var [[@doctodo var_type:_parent]] [[@doctodo var_description:_parent]]
     */
    protected $_parent;
    /**
     * @var [[@doctodo var_type:_child]] [[@doctodo var_description:_child]]
     */
    protected $_child;
    /**
     * @var [[@doctodo var_type:_cache]] [[@doctodo var_description:_cache]]
     */
    protected static $_cache = [];
    /**
     * @var [[@doctodo var_type:_defaultOptions]] [[@doctodo var_description:_defaultOptions]]
     */
    protected $_defaultOptions = [
        'required' => false,
        'handlePrimary' => true, // should we look at parent and child primary preferences
        'taxonomy' => null,
        'temporal' => false,
        'activeAble' => false,
        'type' => self::HAS_MANY,
        'parentInherit' => false,
    ];
    /**
     * @var [[@doctodo var_type:_options]] [[@doctodo var_description:_options]]
     */
    protected $_options = [];
    /**
     * @var [[@doctodo var_type:_relationships]] [[@doctodo var_description:_relationships]]
     */
    protected static $_relationships = [];

    /**
     * [[@doctodo method_description:clearCache]].
     */
    public static function clearCache()
    {
        self::$_cache = [];
        self::$_relationships = [];
    }
    /**
     * [[@doctodo method_description:package]].
     *
     * @return [[@doctodo return_type:package]] [[@doctodo return_description:package]]
     */
    public function package()
    {
        return [
            'id' => $this->systemId,
            'temporal' => $this->temporal,
            'taxonomy' => $this->taxonomyPackage,
            'activeAble' => $this->activeAble,
            'type' => $this->type,
        ];
    }

    /**
     * [[@doctodo method_description:doHandlePrimary]].
     *
     * @param [[@doctodo param_type:role]] $role [[@doctodo param_description:role]] [optional]
     *
     * @return [[@doctodo return_type:doHandlePrimary]] [[@doctodo return_description:doHandlePrimary]]
     */
    public function doHandlePrimary($role = null)
    {
        if (!$this->handlePrimary) {
            return false;
        }

        if (in_array($role, ['child', self::ROLE_CHILD])
            && $this->handlePrimary === self::ROLE_CHILD) {
            return true;
        }

        if (in_array($role, ['parent', self::ROLE_PARENT])
            && $this->handlePrimary === self::ROLE_PARENT) {
            return true;
        }

        return false;
    }

    /**
     * Get taxonomy package.
     *
     * @return [[@doctodo return_type:getTaxonomyPackage]] [[@doctodo return_description:getTaxonomyPackage]]
     */
    public function getTaxonomyPackage()
    {
        if (empty($this->taxonomy)) {
            return false;
        }
        $taxonomySettings = $this->taxonomy;
        if (!is_array($taxonomySettings)) {
            $taxonomySettings = ['id' => $taxonomySettings];
        }
        $taxonomy = Yii::$app->collectors['taxonomies']->getOne($taxonomySettings['id']);
        if (empty($taxonomy) || empty($taxonomy->object)) {
            return false;
        }

        return $taxonomy->package($taxonomySettings);
    }

    /**
     * Get primary object.
     *
     * @param [[@doctodo param_type:primaryObject]] $primaryObject [[@doctodo param_description:primaryObject]]
     * @param [[@doctodo param_type:relatedObject]] $relatedObject [[@doctodo param_description:relatedObject]]
     * @param [[@doctodo param_type:role]]          $role          [[@doctodo param_description:role]]
     *
     * @return [[@doctodo return_type:getPrimaryObject]] [[@doctodo return_description:getPrimaryObject]]
     */
    public function getPrimaryObject($primaryObject, $relatedObject, $role)
    {
        if (!$this->handlePrimary) {
            return false;
        }
        if ($role === 'child') {
            $primaryField = 'primary_child';
            if (!$relatedObject->objectType->getPrimaryAsChild($this->parent)) {
                // \d(['bad', $this->systemId, get_class($primaryObject), get_class($relatedObject), $role]);
                return false;
            }
            $primaryParent = $primaryObject;
        } else {
            $primaryField = 'primary_parent';
            if (!$relatedObject->objectType->getPrimaryAsParent($this->child)) {
                // \d(['bad', $this->systemId, get_class($primaryObject), get_class($relatedObject), $role]);
                return false;
            }
            $primaryParent = $relatedObject;
        }

        $key = json_encode([__FUNCTION__, $this->systemId, $primaryObject->primaryKey]);
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`' . $alias . '`.`parent_object_id`' => $primaryParent->primaryKey, '`' . $alias . '`.`' . $primaryField . '`' => 1]);
            $relation->andWhere(['or', '`' . $alias . '`.`child_object_id` LIKE :prefix']); //, '`'. $alias.'`.`child_object_id` LIKE \''.$childClass.'\''
            $relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
            $primaryObject->addActiveConditions($relation, $alias);
            // \d([$this->systemId, $relation->createCommand()->rawSql, $primaryField, $role]);
            $relation = $relation->one();
            if (!empty($relation)) {
                self::$_cache[$key] = $relation;
            }
        }

        return self::$_cache[$key];
    }

    /**
     * Get primary child.
     *
     * @param [[@doctodo param_type:parentObject]] $parentObject [[@doctodo param_description:parentObject]]
     *
     * @return [[@doctodo return_type:getPrimaryChild]] [[@doctodo return_description:getPrimaryChild]]
     */
    public function getPrimaryChild($parentObject)
    {
        if (!$this->handlePrimary) {
            return false;
        }
        if (!$this->child->getPrimaryAsChild($this->parent)) {
            return false;
        }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObject->primaryKey]);
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`' . $alias . '`.`parent_object_id`' => $parentObject->primaryKey, '`' . $alias . '`.`primary_child`' => 1]);
            $relation->andWhere(['or', '`' . $alias . '`.`child_object_id` LIKE :prefix']); //, '`'. $alias.'`.`child_object_id` LIKE \''.$childClass.'\''
            $relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
            $parentObject->addActiveConditions($relation, $alias);
            $relation = $relation->one();
            if (!empty($relation)) {
                self::$_cache[$key] = $relation;
            }
        }

        return self::$_cache[$key];
    }

    /**
     * Get primary parent.
     *
     * @param [[@doctodo param_type:parentObject]] $parentObject [[@doctodo param_description:parentObject]]
     *
     * @return [[@doctodo return_type:getPrimaryParent]] [[@doctodo return_description:getPrimaryParent]]
     */
    public function getPrimaryParent($parentObject)
    {
        if (!$this->handlePrimary) {
            return false;
        }
        if (!$this->parent->getPrimaryAsParent($this->child)) {
            return false;
        }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObject->primaryKey]);
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`' . $alias . '`.`parent_object_id`' => $parentObject->primaryKey, '`' . $alias . '`.`primary_parent`' => 1]);
            $relation->andWhere('`' . $alias . '`.`child_object_id` LIKE :prefix');
            $relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
            $parentObject->addActiveConditions($relation, $alias);
            $relation = $relation->one();
            if (!empty($relation)) {
                self::$_cache[$key] = $relation;
            }
        }

        return self::$_cache[$key];
    }

    /**
     * Constructor.
     *
     * @param object  $parent
     * @param object  $child
     * @param unknown $options (optional)
     */
    public function __construct(Item $parent, Item $child, $options = [])
    {
        $this->_parent = $parent;
        $this->_child = $child;
        $this->mergeOptions($options);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        } elseif (array_key_exists($name, $this->_defaultOptions)) {
            return $this->_defaultOptions[$name];
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->_options)) {
            return isset($this->_options[$name]);
        } elseif (array_key_exists($name, $this->_defaultOptions)) {
            return isset($this->_defaultOptions[$name]);
        }

        return parent::__get($name);
    }

    /**
     * Get one.
     *
     * @param cascade\components\types\Item $parent  [[@doctodo param_description:parent]]
     * @param cascade\components\types\Item $child   [[@doctodo param_description:child]]
     * @param array                         $options [[@doctodo param_description:options]] [optional]
     *
     * @return [[@doctodo return_type:getOne]] [[@doctodo return_description:getOne]]
     */
    public static function getOne(Item $parent, Item $child, $options = [])
    {
        $key = md5($parent->systemId . "." . $child->systemId);
        if (isset(self::$_relationships[$key])) {
            self::$_relationships[$key]->mergeOptions($options);
        } else {
            self::$_relationships[$key] = new Relationship($parent, $child, $options);
        }

        return self::$_relationships[$key];
    }

    /**
     * Get by.
     *
     * @param [[@doctodo param_type:relationshipId]] $relationshipId [[@doctodo param_description:relationshipId]]
     *
     * @return [[@doctodo return_type:getById]] [[@doctodo return_description:getById]]
     */
    public static function getById($relationshipId)
    {
        $key = md5($relationshipId);
        if (isset(self::$_relationships[$key])) {
            return self::$_relationships[$key];
        }

        return false;
    }

    /**
     * [[@doctodo method_description:has]].
     *
     * @param cascade\components\types\Item $parent [[@doctodo param_description:parent]]
     * @param cascade\components\types\Item $child  [[@doctodo param_description:child]]
     *
     * @return [[@doctodo return_type:has]] [[@doctodo return_description:has]]
     */
    public static function has(Item $parent, Item $child)
    {
        $key = md5($parent->systemId . "." . $child->systemId);

        return isset(self::$_relationships[$key]);
    }

    /**
     * Get has fields.
     *
     * @return [[@doctodo return_type:getHasFields]] [[@doctodo return_description:getHasFields]]
     */
    public function getHasFields()
    {
        return ($this->temporal || $this->activeAble || $this->taxonomyPackage);
    }

    /**
     * [[@doctodo method_description:isHasOne]].
     *
     * @return [[@doctodo return_type:isHasOne]] [[@doctodo return_description:isHasOne]]
     */
    public function isHasOne()
    {
        return $this->type === self::HAS_ONE;
    }

    /**
     * [[@doctodo method_description:isHasMany]].
     *
     * @return [[@doctodo return_type:isHasMany]] [[@doctodo return_description:isHasMany]]
     */
    public function isHasMany()
    {
        return $this->type === self::HAS_MANY;
    }

    /**
     * [[@doctodo method_description:companionRole]].
     *
     * @param [[@doctodo param_type:queryRole]] $queryRole [[@doctodo param_description:queryRole]]
     *
     * @return [[@doctodo return_type:companionRole]] [[@doctodo return_description:companionRole]]
     */
    public function companionRole($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return 'parent';
        }

        return 'child';
    }

    /**
     * Get label.
     *
     * @param [[@doctodo param_type:role]] $role [[@doctodo param_description:role]]
     *
     * @return [[@doctodo return_type:getLabel]] [[@doctodo return_description:getLabel]]
     */
    public function getLabel($role)
    {
        $role = $this->companionRole($role);
        if ($role === 'child') {
            return 'Child ' . $this->child->title->upperSingular;
        } else {
            return 'Parent ' . $this->parent->title->upperSingular;
        }
    }

    /**
     * Get nice.
     *
     * @param [[@doctodo param_type:queryRole]] $queryRole [[@doctodo param_description:queryRole]]
     *
     * @return [[@doctodo return_type:getNiceId]] [[@doctodo return_description:getNiceId]]
     */
    public function getNiceId($queryRole)
    {
        $roleType = $this->roleType($queryRole);
        if (empty($roleType)) {
            return false;
        }

        return implode(':', [$this->role($queryRole), $roleType->systemId]);
    }

    /**
     * Get companion nice.
     *
     * @param [[@doctodo param_type:queryRole]] $queryRole [[@doctodo param_description:queryRole]]
     *
     * @return [[@doctodo return_type:getCompanionNiceId]] [[@doctodo return_description:getCompanionNiceId]]
     */
    public function getCompanionNiceId($queryRole)
    {
        $companionRoleType = $this->companionRoleType($queryRole);
        if (empty($companionRoleType)) {
            return false;
        }

        return implode(':', [$this->companionRole($queryRole), $companionRoleType->systemId]);
    }

    /**
     * [[@doctodo method_description:companionRoleType]].
     *
     * @param [[@doctodo param_type:queryRole]] $queryRole [[@doctodo param_description:queryRole]]
     *
     * @return [[@doctodo return_type:companionRoleType]] [[@doctodo return_description:companionRoleType]]
     */
    public function companionRoleType($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return $this->parent;
        }

        return $this->child;
    }

    /**
     * [[@doctodo method_description:role]].
     *
     * @param [[@doctodo param_type:queryRole]] $queryRole [[@doctodo param_description:queryRole]]
     *
     * @return [[@doctodo return_type:role]] [[@doctodo return_description:role]]
     */
    public function role($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return 'child';
        }

        return 'parent';
    }

    /**
     * [[@doctodo method_description:roleType]].
     *
     * @param [[@doctodo param_type:queryRole]] $queryRole [[@doctodo param_description:queryRole]]
     *
     * @return [[@doctodo return_type:roleType]] [[@doctodo return_description:roleType]]
     */
    public function roleType($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return $this->child;
        }

        return $this->parent;
    }

    /**
     * [[@doctodo method_description:canLink]].
     *
     * @param [[@doctodo param_type:relationshipRole]] $relationshipRole [[@doctodo param_description:relationshipRole]]
     * @param [[@doctodo param_type:object]]           $object           [[@doctodo param_description:object]]
     *
     * @return [[@doctodo return_type:canLink]] [[@doctodo return_description:canLink]]
     */
    public function canLink($relationshipRole, $object)
    {
        $objectModule = $object->objectType;
        if (!$objectModule
            || ($relationshipRole === 'parent' && ($this->child->uniparental || $this->isHasOne()))
        ) {
            return false;
        }

        if (!$object->can('associate:' . $this->companionRoleType($relationshipRole)->systemId)) {
            return false;
        }

        return true;
    }

    /**
     * [[@doctodo method_description:canCreate]].
     *
     * @param [[@doctodo param_type:relationshipRole]] $relationshipRole [[@doctodo param_description:relationshipRole]]
     * @param [[@doctodo param_type:object]]           $object           [[@doctodo param_description:object]]
     *
     * @return [[@doctodo return_type:canCreate]] [[@doctodo return_description:canCreate]]
     */
    public function canCreate($relationshipRole, $object)
    {
        $objectModule = $object->objectType;
        if ($this->child->hasDashboard && $relationshipRole === 'child') { // && ($this->parent->uniparental || $this->uniqueParent)

            return false;
        }

        return true;
    }

    /**
     * Get model.
     *
     * @param [[@doctodo param_type:parentObjectId]] $parentObjectId [[@doctodo param_description:parentObjectId]]
     * @param [[@doctodo param_type:childObjectId]]  $childObjectId  [[@doctodo param_description:childObjectId]]
     * @param boolean                                $activeOnly     [[@doctodo param_description:activeOnly]] [optional]
     *
     * @return [[@doctodo return_type:getModel]] [[@doctodo return_description:getModel]]
     */
    public function getModel($parentObjectId, $childObjectId, $activeOnly = true)
    {
        if (is_object($parentObjectId)) {
            $parentObjectId = $parentObjectId->primaryKey;
        }
        if (is_object($childObjectId)) {
            $childObjectId = $childObjectId->primaryKey;
        }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObjectId, $activeOnly]);
        if (!isset(self::$_cache[$key])) {
            $relationClass = Yii::$app->classes['Relation'];
            $all = $relationClass::find();
            $all->where(
                ['or', 'parent_object_id=:parentObjectId', 'child_object_id=:childObjectId']
            );
            $all->params[':parentObjectId'] = $parentObjectId;
            $all->params[':childObjectId'] = $childObjectId;
            if ($activeOnly) {
                Relatable::doAddActiveConditions($all, false);
            }
            $all = $all->all();
            foreach ($all as $relation) {
                $subkey = json_encode([__FUNCTION__, $this->systemId, $relation->parent_object_id, $activeOnly]);
                if (!isset(self::$_cache[$subkey])) {
                    self::$_cache[$subkey] = [];
                }
                self::$_cache[$subkey][$relation->child_object_id] = $relation;
            }
        }
        if (isset(self::$_cache[$key]) && isset(self::$_cache[$key][$childObjectId])) {
            return self::$_cache[$key][$childObjectId];
        }

        return false;
    }

    /**
     * [[@doctodo method_description:mergeOptions]].
     *
     * @param unknown $newOptions
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     */
    public function mergeOptions($newOptions)
    {
        foreach ($newOptions as $k => $v) {
            if (array_key_exists($k, $this->_options)) {
                if ($this->_options[$k] !== $v) {
                    throw new Exception("Conflicting relationship settings between parent: {$this->parent->name} and child: {$this->child->name}!");
                }
            } else {
                $this->_options[$k] = $v;
            }
        }
        $this->_options = array_merge($this->_options, $newOptions);
    }

    /**
     * Set default options.
     *
     * @return [[@doctodo return_type:setDefaultOptions]] [[@doctodo return_description:setDefaultOptions]]
     */
    public function setDefaultOptions()
    {
        foreach ($this->_defaultOptions as $k => $v) {
            if (!array_key_exists($k, $this->_options)) {
                $this->_options[$k] = $v;
            }
        }

        return true;
    }

    /**
     * Get parent.
     *
     * @return unknown
     */
    public function getParent()
    {
        return $this->_parent->object;
    }

    /**
     * Get child.
     *
     * @return unknown
     */
    public function getChild()
    {
        return $this->_child->object;
    }

    /**
     * Get related object.
     *
     * @param [[@doctodo param_type:baseObject]]      $baseObject      [[@doctodo param_description:baseObject]]
     * @param [[@doctodo param_type:baseRole]]        $baseRole        [[@doctodo param_description:baseRole]]
     * @param [[@doctodo param_type:primaryRelation]] $primaryRelation [[@doctodo param_description:primaryRelation]] [optional]
     *
     * @return [[@doctodo return_type:getRelatedObject]] [[@doctodo return_description:getRelatedObject]]
     */
    public function getRelatedObject($baseObject, $baseRole, $primaryRelation = null)
    {
        $companionRole = $this->companionRole($baseRole);
        $companionType = $this->companionRoleType($baseRole);
        $companionModel = $companionType->primaryModel;
        if (!isset($primaryRelation) || is_array($primaryRelation)) {
            if (!is_array($primaryRelation)) {
                $primaryRelation = [];
            }
            $primaryRelation = $this->getPrimaryRelation($baseObject, $baseRole, $primaryRelation);
        }
        if (!empty($primaryRelation)) {
            if ($companionRole === 'child') {
                return $primaryRelation->childObject;
            } else {
                return $primaryRelation->parentObject;
            }
        }

        return false;
    }

    /**
     * Get primary relation.
     *
     * @param [[@doctodo param_type:baseObject]] $baseObject      [[@doctodo param_description:baseObject]]
     * @param [[@doctodo param_type:baseRole]]   $baseRole        [[@doctodo param_description:baseRole]]
     * @param array                              $relationOptions [[@doctodo param_description:relationOptions]] [optional]
     *
     * @return [[@doctodo return_type:getPrimaryRelation]] [[@doctodo return_description:getPrimaryRelation]]
     */
    public function getPrimaryRelation($baseObject, $baseRole, $relationOptions = [])
    {
        $companionRole = $this->companionRole($baseRole);
        $companionType = $this->companionRoleType($baseRole);
        $companionModel = $companionType->primaryModel;
        if (!isset($relationOptions['order'])) {
            $relationOptions['order'] = [];
        }
        if ($companionRole === 'child') {
            array_unshift($relationOptions['order'], ['primary_child', SORT_DESC]);
            $relation = $baseObject->queryParentRelations($companionModel, $relationOptions)->one();
        } else {
            array_unshift($relationOptions['order'], ['primary_parent', SORT_DESC]);
            $relation = $baseObject->queryParentRelations($companionModel, $relationOptions)->one();
        }
        if (empty($relation)) {
            return false;
        } else {
            return $relation;
        }
    }

    /**
     * Get active.
     *
     * @return unknown
     */
    public function getActive()
    {
        return (isset($this->_child) and $this->_child->active) and (isset($this->_parent) and $this->_parent->active);
    }

    /**
     * Get options.
     *
     * @return [[@doctodo return_type:getOptions]] [[@doctodo return_description:getOptions]]
     */
    public function getOptions()
    {
        return array_merge($this->_defaultOptions, $this->_options);
    }

    /**
     * Get system.
     *
     * @return [[@doctodo return_type:getSystemId]] [[@doctodo return_description:getSystemId]]
     */
    public function getSystemId()
    {
        return $this->_parent->systemId . '.' . $this->_child->systemId;
    }
}
