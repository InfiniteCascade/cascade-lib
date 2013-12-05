<?php

namespace cascade\components\taxonomy;

use infinite\base\exceptions\Exception;
use infinite\helpers\ArrayHelper;

class Item extends \infinite\base\collector\Item {
	public $name;
	public $systemId;
	public $systemVersion = 1;
	public $initialTaxonomies = [];
	public $model;
	public $forModel;
	public $multiple = false;
	public $required = false;
	public $default = [];
	public $parentUnique = false;

	protected $_taxonomies;

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getTaxonomies() {
		if (is_null($this->_taxonomies)) {
			$this->_taxonomies = $this->model->taxonomies;
		}
		return $this->_taxonomies;
	}
	/**
	 *
	 *
	 * @return unknown
	 */
	public function getTaxonomyList() {
		return ArrayHelper::map($this->getTaxonomies(), 'id', 'name');
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getTaxonomy($system_id) {
		foreach ($this->getTaxonomies() as $taxonomy) {
			if ($taxonomy->system_id === $system_id) {
				return $taxonomy;
			}
		}
		return false;
	}

	public function addTaxonomy($taxonomy) {
		$this->taxonomies;
		if (is_null($this->_taxonomies)) {
			$this->taxonomies;
		}
		$this->_taxonomies[] = $taxonomy;
	}
}


?>
