<?php
/**
 * ./app/components/objects/RObjectRelationship.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */
namespace cascade\components\types;

use Yii;

use infinite\base\exceptions\Exception;

class Relationship extends \infinite\base\Object
{
	const HAS_MANY = 0x01;
	const HAS_ONE = 0x02;

	protected $_parent;
	protected $_child;
	static $_cache = [];

	protected $_defaultOptions = [
		'required' => false,
		'handlePrimary' => true, // should we look at parent and child primary preferences
		'taxonomy' => null,
		'temporal' => false,
		'activeAble' => false,
		'type' => self::HAS_MANY
	];
	protected $_options = [];
	static $_relationships = [];

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

	public function getPrimaryChild($parentObject)
	{
		if (!$this->handlePrimary) { return false; }
		if (!$this->child->primaryAsChild) { return false; }
		$key = json_encode([__FUNCTION__, $this->systemId, $parentObject->primaryKey]);
		if (!isset(self::$_cache[$key])) {
			self::$_cache[$key] = true;
			$relationClass = Yii::$app->classes['Relation'];
			$childClass = $this->child->primaryModel;
			$relation = $relationClass::find();
			$alias = $relationClass::tableName();
			$relation->andWhere(['`'. $alias.'`.`parent_object_id`' => $parentObject->primaryKey, '`'. $alias.'`.`primary`' => 1]);
			$relation->andWhere(['or', '`'. $alias.'`.`child_object_id` LIKE :prefix']); //, '`'. $alias.'`.`child_object_id` LIKE \''.$childClass.'\''
			$relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
			$parentObject->addActiveConditions($relation, $alias);
			$relation = $relation->one();
			if ($relation) {
				self::$_cache[$key] = $relation;
			}
		}
		return self::$_cache[$key];
	}


	public function getPrimaryParent($childObject)
	{
		if (!$this->handlePrimary) { return false; }
		if (!$this->parent->primaryAsChild) { return false; }
		$key = json_encode([__FUNCTION__, $this->systemId, $childObject->primaryKey]);
		if (!isset(self::$_cache[$key])) {
			self::$_cache[$key] = false;
			$relationClass = Yii::$app->classes['Relation'];
			$parentClass = $this->parent->primaryModel;
			$relation = $relationClass::find();
			$alias = $relationClass::tableName();
			$relation->andWhere(['`'. $alias.'`.`child_object_id`' => $childObject->primaryKey, '`'. $alias.'`.`primary`' => 1]);
			$relation->andWhere('`'. $alias.'`.`parent_object_id` LIKE :prefix');
			$relation->params[':prefix'] = $parentClass::modelPrefix() . '-%';
			$childObject->addActiveConditions($relation, $alias);
			$relation = $relation->one();
			if ($relation) {
				self::$_cache[$key] = $relation;
			}
		}
		return self::$_cache[$key];
	}

	/**
	 *
	 *
	 * @param object  $parent
	 * @param object  $child
	 * @param unknown $options (optional)
	 */
	public function __construct(Item $parent, Item $child, $options = []) {
		$this->_parent = $parent;
		$this->_child = $child;
		$this->mergeOptions($options);
	}

	public function __get($name) {
		if (array_key_exists($name, $this->_options)) {
			return $this->_options[$name];
		} elseif (array_key_exists($name, $this->_defaultOptions)) {
			return $this->_defaultOptions[$name];
		}
		return parent::__get($name);
	}

	public function __isset($name) {
		if (array_key_exists($name, $this->_options)) {
			return isset($this->_options[$name]);
		} elseif (array_key_exists($name, $this->_defaultOptions)) {
			return isset($this->_defaultOptions[$name]);
		}
		return parent::__get($name);
	}

	/**
	 *
	 *
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

	static public function getById($relationshipId)
	{
		$key = md5($relationshipId);
		if (isset(self::$_relationships[$key])) {
			return self::$_relationships[$key];
		}
		return false;
	}
	
	static public function has(Item $parent, Item $child)
	{
		$key = md5($parent->systemId .".". $child->systemId);
		return isset(self::$_relationships[$key]);
	}

	public function getHasFields()
	{
		return ($this->temporal || $this->activeAble || $this->taxonomyPackage);
	}

	public function isHasOne()
	{
		return $this->type === self::HAS_ONE;
	}

	public function isHasMany()
	{
		return $this->type === self::HAS_MANY;
	}

	public function companionRole($queryRole)
	{
		if ($queryRole === 'children' || $queryRole === 'child') {
			return 'parent';
		}
		return 'child';
	}

	public function companionRoleType($queryRole)
	{
		if ($queryRole === 'children' || $queryRole === 'child') {
			return $this->parent;
		}
		return $this->child;
	}

	public function roleType($queryRole)
	{
		if ($queryRole === 'children' || $queryRole === 'child') {
			return $this->child;
		}
		return $this->parent;
	}

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

	public function canCreate($relationshipRole, $object)
	{
		$objectModule = $object->objectType;
		if ($this->child->hasDashboard && $relationshipRole === 'child') { // && ($this->parent->uniparental || $this->uniqueParent)
			return false;
		}
		return true;
	}

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
	 *
	 *
	 * @param unknown $newOptions
	 */
	public function mergeOptions($newOptions) {
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

	public function setDefaultOptions() {
		foreach ($this->_defaultOptions as $k => $v) {
			if (!array_key_exists($k, $this->_options)) {
				$this->_options[$k] = $v;
			}
		}
		return true;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getParent() {
		return $this->_parent->object;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getChild() {
		return $this->_child->object;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getActive() {
		return (isset($this->_child) AND $this->_child->active) and (isset($this->_parent) AND $this->_parent->active);
	}

	public function getOptions() {
		return array_merge($this->_defaultOptions, $this->_options);
	}

	public function getSystemId()
	{
		return $this->_parent->systemId .'.'. $this->_child->systemId;
	}
}


?>
