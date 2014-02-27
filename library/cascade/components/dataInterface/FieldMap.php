<?php
namespace cascade\components\dataInterface;

use cascade\models\Registry;
use cascade\models\Relation;
use cascade\models\KeyTranslation;
use cascade\components\dataInterface\Action;

use infinite\helpers\ArrayHelper;

class FieldMap extends \infinite\base\Object {
	public $dataSource;

	public $localField = false;
	public $foreignField = false;
	public $foreignModel = false;
	public $searchFields;
	public $value;
	public $filter;
	public $taxonomy;

	public function extractValue($foreignModel = null)
	{
		if (is_null($foreignModel)) {
			$foreignModel = $this->foreignModel;
		}

		$value = null;
		if (isset($this->value)) {
			if (is_callable($this->value)) {
				$value = call_user_func($this->value, $foreignModel, $this);
			} else {
				$value = $this->value;
			}
		} elseif (isset($this->foreignField)) {
			if (is_string($this->foreignField)) {
				$value = (isset($foreignModel->{$this->foreignField}) ? $foreignModel->{$this->foreignField} : null);
			} elseif (is_callable($this->foreignField)) {
				$value = call_user_func($this->foreignField, $foreignModel);
			}
		}
		if (isset($this->filter)) {
			$value = call_user_func($this->filter, $value);
		}
		return $value;
	}
}
?>