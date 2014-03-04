<?php
namespace cascade\components\web;

class ObjectViewEvent extends \yii\base\Event
{
	public $action;
	public $accessed = true;
	protected $_object;
	protected $_objectType;

	public function handleWith($callable, $always = false)
	{
		if ($this->handled && !$always) { return false; }
		if (!is_callable($callable)) { return false; }
		call_user_func($callable, $this);
		return false;
	}

	public function setObject($object)
	{
		if (is_null($this->_objectType)) {
			$this->objectType = $object->objectType;
		}
		$this->_object = $object;
	}

	public function getObject()
	{
		return $this->_object;
	}

	public function setObjectType($type)
	{
		if (!is_object($type)) {
			if (Yii::$app->collectors['types']->has($type)) {
				$type = Yii::$app->collectors['types']->getOne($type)->object;
			} else {
				$type = null;
			}
		}
		$this->_objectType = $type;
	}

	public function getObjectType()
	{
		return $this->_objectType;
	}
}
?>