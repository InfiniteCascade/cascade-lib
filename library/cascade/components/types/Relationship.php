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

	public static $relationClass = 'cascade\\models\\Relation';

	protected $_parent;
	protected $_child;
	static $_cache = [];

	protected $_defaultOptions = [
		'required' => false,
		'handlePrimary' => true,
		'taxonomy' => null,
		'fields' => [],
		'type' => self::HAS_MANY
	];
	protected $_options = [];
	static $_relationships = [];

	public function getPrimaryRelation($parentObject)
	{
		if (!$this->handlePrimary) { return false; }
		$relationClass = self::$relationClass;
		$childClass = $this->child->primaryModel;
		$relation = $relationClass::find();
		$alias = $relationClass::tableName();
		$relation->andWhere(['`'. $alias.'`.`parent_object_id`' => $parentObject->primaryKey, '`'. $alias.'`.`primary`' => 1]);
		$relation->andWhere('`'. $alias.'`.`child_object_id` LIKE :prefix');
		$relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
		$parentObject->addActiveConditions($relation, $alias);
		$relation = $relation->one();
		if ($relation) {
			return $relation;
		}
		return null;
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
		$key = md5($parent->systemId ."-". $child->systemId);
		if (isset(self::$_relationships[$key])) {
			self::$_relationships[$key]->mergeOptions($options);
		} else {
			self::$_relationships[$key] = new Relationship($parent, $child, $options);
		}
		return self::$_relationships[$key];
	}
	
	static public function has(Item $parent, Item $child)
	{
		$key = md5($parent->systemId ."-". $child->systemId);
		return isset(self::$_relationships[$key]);
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

	public function canLink($relationshipRole, $objectModule)
	{
		if (!$objectModule || ($relationshipRole === 'parent' && $this->child->uniparental)) {
			return false;
		}
		return true;
	}

	public function canCreate($relationshipRole, $objectModule)
	{
		if ($this->child->hasDashboard && $relationshipRole === 'child') { // && ($this->parent->uniparental || $this->uniqueParent)
			return false;
		}
		return true;
	}

	public function getModel($parentObjectId, $childObjectId)
	{
		if (!isset(self::$_cache[$parentObjectId])) {
			$relationClass = self::$relationClass;
			$all = $relationClass::find();
			$all->where(
				['or', 'parent_object_id=:parentObjectId', 'child_object_id=:childObjectId']
			);
			$all->params[':parentObjectId'] = $parentObjectId;
			$all->params[':childObjectId'] = $childObjectId;
			$all = $all->all();
			foreach ($all as $relation) {
				self::$_cache[$relation->parent_object_id][$relation->child_object_id] = $relation;
			}
		}
		if (isset(self::$_cache[$parentObjectId]) && isset(self::$_cache[$parentObjectId][$childObjectId])) {
			return self::$_cache[$parentObjectId][$childObjectId];
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
		return $this->_parent->systemId .'-'. $this->_child->systemId;
	}
}


?>
