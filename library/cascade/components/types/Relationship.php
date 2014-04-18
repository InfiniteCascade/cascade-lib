<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use Yii;

use infinite\base\exceptions\Exception;

/**
 * Relationship [@doctodo write class description for Relationship]
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
     * @var __var__parent_type__ __var__parent_description__
     */
    protected $_parent;
    /**
     * @var __var__child_type__ __var__child_description__
     */
    protected $_child;
    /**
     * @var __var__cache_type__ __var__cache_description__
     */
    static $_cache = [];
    /**
     * @var __var__defaultOptions_type__ __var__defaultOptions_description__
     */
    protected $_defaultOptions = [
        'required' => false,
        'handlePrimary' => true, // should we look at parent and child primary preferences
        'taxonomy' => null,
        'temporal' => false,
        'activeAble' => false,
        'type' => self::HAS_MANY
    ];
    /**
     * @var __var__options_type__ __var__options_description__
     */
    protected $_options = [];
    /**
     * @var __var__relationships_type__ __var__relationships_description__
     */
    static $_relationships = [];

    /**
     * __method_package_description__
     * @return __return_package_type__ __return_package_description__
     */
    public function package()
    {
        return [
            'id' => $this->systemId,
            'temporal' => $this->temporal,
            'taxonomy' => $this->taxonomyPackage,
            'activeAble' => $this->activeAble,
            'type' => $this->type
        ];
    }

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
     * Get taxonomy package
     * @return __return_getTaxonomyPackage_type__ __return_getTaxonomyPackage_description__
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

    public function getPrimaryObject($primaryObject, $relatedObject, $role)
    {
        if (!$this->handlePrimary) { return false; }
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
            $relation->andWhere(['`'. $alias.'`.`parent_object_id`' => $primaryParent->primaryKey, '`'. $alias.'`.`'.$primaryField.'`' => 1]);
            $relation->andWhere(['or', '`'. $alias.'`.`child_object_id` LIKE :prefix']); //, '`'. $alias.'`.`child_object_id` LIKE \''.$childClass.'\''
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
     * Get primary child
     * @param __param_parentObject_type__     $parentObject __param_parentObject_description__
     * @return __return_getPrimaryChild_type__ __return_getPrimaryChild_description__
     */
    public function getPrimaryChild($parentObject)
    {
        if (!$this->handlePrimary) { return false; }
        if (!$this->child->getPrimaryAsChild($this->parent)) { return false; }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObject->primaryKey]);
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`'. $alias.'`.`parent_object_id`' => $parentObject->primaryKey, '`'. $alias.'`.`primary_child`' => 1]);
            $relation->andWhere(['or', '`'. $alias.'`.`child_object_id` LIKE :prefix']); //, '`'. $alias.'`.`child_object_id` LIKE \''.$childClass.'\''
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
     * Get primary parent
     * @param __param_childObject_type__       $childObject __param_childObject_description__
     * @return __return_getPrimaryParent_type__ __return_getPrimaryParent_description__
     */
    public function getPrimaryParent($parentObject)
    {
        if (!$this->handlePrimary) { return false; }
        if (!$this->parent->getPrimaryAsParent($this->child)) { return false; }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObject->primaryKey]); 
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`'. $alias.'`.`parent_object_id`' => $parentObject->primaryKey, '`'. $alias.'`.`primary_parent`' => 1]);
            $relation->andWhere('`'. $alias.'`.`child_object_id` LIKE :prefix');
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
     * Get one
     * @param object  $parent
     * @param object  $child
     * @param unknown $options (optional)
     * @return unknown
     */
    static public function getOne(Item $parent, Item $child, $options = [])
    {
        $key = md5($parent->systemId .".". $child->systemId);
        if (isset(self::$_relationships[$key])) {
            self::$_relationships[$key]->mergeOptions($options);
        } else {
            self::$_relationships[$key] = new Relationship($parent, $child, $options);
        }

        return self::$_relationships[$key];
    }

    /**
     * Get by
     * @param __param_relationshipId_type__ $relationshipId __param_relationshipId_description__
     * @return __return_getById_type__       __return_getById_description__
     */
    static public function getById($relationshipId)
    {
        $key = md5($relationshipId);
        if (isset(self::$_relationships[$key])) {
            return self::$_relationships[$key];
        }

        return false;
    }

    /**
     * __method_has_description__
     * @param cascade\components\types\Item $parent __param_parent_description__
     * @param cascade\components\types\Item $child  __param_child_description__
     * @return __return_has_type__           __return_has_description__
     */
    static public function has(Item $parent, Item $child)
    {
        $key = md5($parent->systemId .".". $child->systemId);

        return isset(self::$_relationships[$key]);
    }

    /**
     * Get has fields
     * @return __return_getHasFields_type__ __return_getHasFields_description__
     */
    public function getHasFields()
    {
        return ($this->temporal || $this->activeAble || $this->taxonomyPackage);
    }

    /**
     * __method_isHasOne_description__
     * @return __return_isHasOne_type__ __return_isHasOne_description__
     */
    public function isHasOne()
    {
        return $this->type === self::HAS_ONE;
    }

    /**
     * __method_isHasMany_description__
     * @return __return_isHasMany_type__ __return_isHasMany_description__
     */
    public function isHasMany()
    {
        return $this->type === self::HAS_MANY;
    }

    /**
     * __method_companionRole_description__
     * @param __param_queryRole_type__      $queryRole __param_queryRole_description__
     * @return __return_companionRole_type__ __return_companionRole_description__
     */
    public function companionRole($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return 'parent';
        }

        return 'child';
    }

    /**
     * __method_companionRoleType_description__
     * @param __param_queryRole_type__          $queryRole __param_queryRole_description__
     * @return __return_companionRoleType_type__ __return_companionRoleType_description__
     */
    public function companionRoleType($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return $this->parent;
        }

        return $this->child;
    }

    /**
     * __method_roleType_description__
     * @param __param_queryRole_type__ $queryRole __param_queryRole_description__
     * @return __return_roleType_type__ __return_roleType_description__
     */
    public function roleType($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return $this->child;
        }

        return $this->parent;
    }

    /**
     * __method_canLink_description__
     * @param __param_relationshipRole_type__ $relationshipRole __param_relationshipRole_description__
     * @param __param_object_type__           $object           __param_object_description__
     * @return __return_canLink_type__         __return_canLink_description__
     */
    public function canLink($relationshipRole, $object)
    {
        $objectModule = $object->objectType;
        if (!$objectModule
            || ($relationshipRole === 'parent' && ($this->child->uniparental || $this->isHasOne()))
        ) {
            return false;
        }

        if (!$object->can('associate:'. $this->companionRoleType($relationshipRole)->systemId)) {
            return false;
        }

        return true;
    }

    /**
     * __method_canCreate_description__
     * @param __param_relationshipRole_type__ $relationshipRole __param_relationshipRole_description__
     * @param __param_object_type__           $object           __param_object_description__
     * @return __return_canCreate_type__       __return_canCreate_description__
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
     * Get model
     * @param __param_parentObjectId_type__ $parentObjectId __param_parentObjectId_description__
     * @param __param_childObjectId_type__  $childObjectId  __param_childObjectId_description__
     * @return __return_getModel_type__      __return_getModel_description__
     */
    public function getModel($parentObjectId, $childObjectId)
    {
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObjectId]);
        if (!isset(self::$_cache[$key])) {
            $relationClass = Yii::$app->classes['Relation'];
            $all = $relationClass::find();
            $all->where(
                ['or', 'parent_object_id=:parentObjectId', 'child_object_id=:childObjectId']
            );
            $all->params[':parentObjectId'] = $parentObjectId;
            $all->params[':childObjectId'] = $childObjectId;
            $all = $all->all();
            foreach ($all as $relation) {
                $subkey = json_encode([__FUNCTION__, $this->systemId, $relation->parent_object_id]);
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
     * __method_mergeOptions_description__
     * @param unknown   $newOptions
     * @throws Exception __exception_Exception_description__
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
     * Set default options
     * @return __return_setDefaultOptions_type__ __return_setDefaultOptions_description__
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
     * Get parent
     * @return unknown
     */
    public function getParent()
    {
        return $this->_parent->object;
    }

    /**
     * Get child
     * @return unknown
     */
    public function getChild()
    {
        return $this->_child->object;
    }

    /**
     * Get active
     * @return unknown
     */
    public function getActive()
    {
        return (isset($this->_child) AND $this->_child->active) and (isset($this->_parent) AND $this->_parent->active);
    }

    /**
     * Get options
     * @return __return_getOptions_type__ __return_getOptions_description__
     */
    public function getOptions()
    {
        return array_merge($this->_defaultOptions, $this->_options);
    }

    /**
     * Get system
     * @return __return_getSystemId_type__ __return_getSystemId_description__
     */
    public function getSystemId()
    {
        return $this->_parent->systemId .'.'. $this->_child->systemId;
    }
}
