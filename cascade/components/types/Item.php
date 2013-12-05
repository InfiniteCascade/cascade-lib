<?php
namespace cascade\components\types;

use Yii;
use infinite\helpers\ArrayHelper;
use infinite\base\collector\CollectedObjectInterface;

class Item extends \infinite\base\collector\Item {
	protected $_children = [];
	protected $_parents = [];
	protected $_sections;
	protected $_checked;
	protected $_init = false;

	public function init() {
		parent::init();
		$this->_init = true;
		$this->distributeRelationships();
	}

	public function setObject(CollectedObjectInterface $object) {
		parent::setObject($object);
		$this->distributeRelationships();
		return true;
	}

	protected function distributeRelationships() {
		if (!$this->_init || is_null($this->object)) {
			return;
		}

		foreach ($this->object->children() as $key => $child) {
			$options = array();
			if (is_string($key)) {
				$options = $child;
				$child = $key;
			}
			$this->collector->addRelationship($this->systemId, $child, $options);
		}
		foreach ($this->object->parents() as $key => $parent) {
			$options = array();
			if (is_string($key)) {
				$options = $parent;
				$parent = $key;
			}
			$this->collector->addRelationship($parent, $this->systemId, $options);
		}
	}
	/**
	 *
	 *
	 * @return unknown
	 */
	public function getSections() {
		if (!is_null($this->_sections)) {
			return $this->_sections;
		}
		$this->_sections = array();
		foreach ($this->_children as $rel) {
			if (!$rel->active) { continue; }
			$child = $rel->child;
			$instanceSettings = array('relationship' => $rel, 'queryRole' => 'children');
			$items = Yii::$app->collectors['widgets']->getLocation('parent_objects', $child);
			foreach ($items as $item) {
				$widgetObject = $item->object;
				$item->settings = $instanceSettings;
				$section = $item->getSection($widgetObject, $instanceSettings);
				if (empty($section)) { continue; }
				if (!isset($this->_sections[$item->section->systemId])) {
					$this->_sections[$section->systemId] = $section;
				}
				$this->_sections[$section->systemId]->object->register($this, $item);
			}
		}

		foreach ($this->_parents as $rel) {
			if (!$rel->active) { continue; }
			$parent = $rel->parent;
			$instanceSettings = array('relationship' => $rel, 'queryRole' => 'parents');
			$items = Yii::$app->collectors['widgets']->getLocation('child_objects', $parent);
			foreach ($items as $item) {
				$item->settings = $instanceSettings;
				$section = $item->getSection($this->object);
				if (empty($section)) { continue; }
				if (!isset($this->_sections[$item->section->systemId])) {
					$this->_sections[$section->systemId] = $section;
				}
				$this->_sections[$section->systemId]->object->register($this, $item);
			}
		}
		
		ArrayHelper::multisort($this->_sections, 'displayPriority');
		return $this->_sections;
	}

	public function getWidgets()
	{
		$sections = $this->sections;
		$widgets = [];
		foreach ($this->sections as $section) {
			foreach ($section->object->getAll() as $key => $widget) {
				$widgets[$key] = $widget;
			}
		}
		return $widgets;
	}
	/**
	 *
	 *
	 * @param unknown $name
	 * @param unknown $relationship
	 * @return unknown
	 */
	public function addChild($name, $relationship) {
		$this->_children[$name] = $relationship;
		return true;
	}


	/**
	 *
	 *
	 * @param unknown $name
	 * @param unknown $relationship
	 * @return unknown
	 */
	public function addParent($name, $relationship) {
		$this->_parents[$name] = $relationship;
		return true;
	}

		/**
	 *
	 *
	 * @param unknown $type
	 * @return unknown
	 */
	public function getChild($type) {
		if (isset($this->_children[$type])) {
			return $this->_children[$type];
		}
		return false;
	}


	/**
	 *
	 *
	 * @param unknown $type
	 * @return unknown
	 */
	public function getParent($type) {
		if (isset($this->_parents[$type])) {
			return $this->_parents[$type];
		}
		return false;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getChildren() {
		$children = array();
		foreach ($this->_children as $key => $child) {
			if (!$child->active) { continue; }
			$children[$key] = $child;
		}
		return $children;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getParents() {
		$parents = array();
		foreach ($this->_parents as $key => $parent) {
			if (!$parent->active) { continue; }
			$parents[$key] = $parent;
		}
		return $parents;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getActive() {
		if (!is_null($this->hasObject()) && $this->checked) {
			return true;
		}
		return false;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getChecked() {
		if (is_null($this->object) || !$this->object) { return false; }
		if (is_null($this->_checked)) {
			$this->_checked = true;
			foreach ($this->object->dependencies() as $dep) {
				if (!$this->collector->has($dep, false)) {
					$this->_checked = false;
				}
			}
		}
		return $this->_checked;
	}
}


?>
