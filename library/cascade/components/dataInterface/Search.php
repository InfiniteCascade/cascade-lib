<?php
namespace cascade\components\dataInterface;

use infinite\helpers\ArrayHelper;

class Search extends \infinite\base\Component {
	public static $interactive = true;
	public $threshold = 9;
	public $autoadjust = 1.5;
	public $dataSource;

	protected $_localFields;
	protected $_foreignFields = [];

	public function searchLocal(DataItem $item, $searchParams = [])
	{
		if (!isset($searchParams['searchFields'])) {
			$searchParams['searchFields'] = $this->localFields;
		}
		if (!isset($searchParams['limit'])) {
			$searchParams['limit'] = 5;
		}
		$query = [];
		foreach ($this->foreignFields as $field) {
			if (!empty($item->foreignObject->{$field})) {
				$query[] = $item->foreignObject->{$field};
			}
		}

		if (empty($query)) {
			return false;
		}

		$localClass = $this->dataSource->localModel;
		$searchResults = $localClass::searchTerm(implode(' ', $query), $searchParams);
		foreach ($searchResults as $k => $r) {
			if ($r->score < $this->threshold) {
				unset($searchResults[$k]);
			}
		}

		$searchResults = array_values($searchResults);
		if (empty($searchResults)) {
			return false;
		} elseif (count($searchResults) === 1 
			|| !self::$interactive
			|| $searchResults[0]->score > ($this->threshold * $this->autoadjust)) {
			return $searchResults[0]->object;
		} else {
			\d($query);
			\d($searchResults);exit;
		}
	}

	public function searchForeign(DataItem $item)
	{
		return false;
	}

	public function setLocalFields($value)
	{
		$this->_localFields = $value;
	}

	public function getLocalFields()
	{
		return $this->_localFields;
	}

	public function setForeignFields($value)
	{
		$this->_foreignFields = $value;
	}

	public function getForeignFields()
	{
		return $this->_foreignFields;
	}

	public function getModule()
	{
		return $this->dataSource->module;
	}
}
?>