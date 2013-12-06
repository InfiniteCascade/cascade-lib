<?php
namespace cascade\components\db\fields;

use Yii;
use infinite\base\exceptions\Exception;

abstract class Base extends \infinite\base\Object {
	public $formFieldClass;
	public $field;
	public $default;
	public $required = false;
	
	protected $_human;
	protected $_format;
	protected $_label;
	protected $_model;
	protected $_formField;

	public function setFormField($value) {
		if (is_array($value)) {
			if (is_null($this->formFieldClass)) {
				throw new Exception("DB Field incorrectly set up. What is the form class?");
			}
			$config = $value;
			$config['class'] = $this->formFieldClass;
			$config['modelField'] = $this;
			$value = Yii::createObject($config);
		}

		$this->_formField = $value;
		return true;
	}


	public function init() {
		parent::init();

		if (!is_null($this->default) && !$this->model->isAttributeChanged($this->field)) {
			$this->model->{$this->field} = $this->default;
		}
	}

	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function setHuman($value) {
		$this->_human = $value;
		return true;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getHuman() {
		if (is_null($this->_human)) {
			$this->_human = HumanFieldDetector::test($this->field);
		}
		return $this->_human;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getFormField() {
		if (is_null($this->_formField)) {
			$this->formField = [];
		}
		return $this->_formField;
	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function getMetaData() {
		if (!$this->model) {
			return false;
		}

		$modelName = get_class($this->model);
		
		if (!isset($modelName::getTableSchema()->columns[$this->field])) {
			return false;
		}

		return $modelName::getTableSchema()->columns[$this->field];
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getModel() {
		if (is_null($this->_model)) {
			return false;
		}
		return $this->_model;
	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function setModel($value) {
		$this->_model = $value;
		return true;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getFormat() {
		if (is_null($this->_format)) {
			return $this->_format = new FormatText($this);
		}
		return $this->_format;
	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function setFormat($value) {
		if (is_array($value)) {
			$this->_format = Yii::createObject($value, $this);
		}
		$this->_format = $value;
		return true;
	}

	public function getFormattedValue() {
		if ($this->format instanceof BaseFormat) {
			return $this->format->get();
		} elseif (is_callable($this->format) OR (is_array($this->format) AND !empty($this->format[0]) AND is_object($this->format[0]))) {
			return $this->evaluateExpression($this->format, array($this->value));
		} else {
			return $this->value;
		}
	}

	public function getValue() {
		return $this->model->{$this->field};
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getLabel() {
		if (is_null($this->_label)) {
			$this->_label = $this->getModel()->getAttributeLabel($this->field);
		}
		return $this->_label;
	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function setLabel($value) {
		$this->_label = $value;
		return true;
	}
}
?>