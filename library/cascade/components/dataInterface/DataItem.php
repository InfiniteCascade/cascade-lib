<?php
namespace cascade\components\dataInterface;

use infinite\base\exceptions\Exception;
use cascade\models\DataInterface;

abstract class DataItem extends \infinite\base\Component {
	public $dataSource;
	public $isForeign = true;

	protected $_pairedDataItem;
	protected $_handledDataItem = false;
	protected $_foreignObject;
	public $foreignPrimaryKey;

	protected $_localObject;
	public $localPrimaryKey;


	const EVENT_LOAD_FOREIGN_OBJECT = 0x01;
	const EVENT_LOAD_LOCAL_OBJECT = 0x02;


	public function clean()
	{
		if (isset($this->foreignObject)) {
			$this->foreignPrimaryKey = $this->foreignObject->primaryKey;
			$this->foreignObject = null;
		}
		if (isset($this->localObject)) {
			$this->localPrimaryKey = $this->localObject->primaryKey;
			$this->localObject = null;
		}
	}

	public function getId()
	{
		if ($this->isForeign) {
			if (isset($this->foreignPrimaryKey)) {
				return $this->foreignPrimaryKey;
			}
		} else {
			if (isset($this->localPrimaryKey)) {
				return $this->localPrimaryKey;
			}
		} 
		if (isset($this->primaryObject)) {
			return $this->primaryObject->primaryKey;
		}
		throw new \Exception("hmmm");
		return null;
	}

	public function handle($fromParent = false)
	{
		if ($this->handledDataItem) { return true; }
		if ($fromParent || !$this->dataSource->childOnly) {
			if ($this->isForeign) {
				// handle local to foreign
				$result = $this->handler->handleForeign();
			} else {
				$result = $this->handler->handleLocal();
			}
		} else {
			$result = true;
		}
		if ($result) {
			$this->handledDataItem = true;
			return $result;
		}
		return false;
	}

	protected function handleLocal()
	{
		return true;
	}


	protected function handleForeign()
	{
		return true;
	}

	public function getHandler()
	{
		if ($this->pairedDataItem) {
			if (!isset($this->primaryObject)) {
				return $this->pairedDataItem;
			} elseif (isset($this->companionObject)) {
				return static::getHandlingObject($this, $this->pairedDataItem);
			}
		}
		return $this;
	}

	public function getHandlingComparison()
	{
		return false;
	}

	public static function getHandlingObject($a, $b)
	{
		$handlingA = $a->handlingComparison;
		$handlingB = $b->handlingComparison;

		if (!$handlingB) {
			return $a;
		}
		if ($handlingA !== false && $handlingB !== false) {
			if ($handlingA > $handlingB) {
				return $a;
			} else {
				return $b;
			}
		}

		return $a;
	}

	public function getPrimaryObject()
	{
		if ($this->isForeign) {
			return $this->foreignObject;
		} else {
			return $this->localObject;
		}
	}

	public function getCompanionObject()
	{
		if ($this->isForeign) {
			return $this->localObject;
		} else {
			return $this->foreignObject;
		}
	}

	public function setCompanionObject($value)
	{
		if ($this->isForeign) {
			return $this->localObject = $value;
		} else {
			return $this->foreignObject = $value;
		}
	}

	public function getCompanionId()
	{
		if ($this->isForeign && isset($this->foreignPrimaryKey)) {
			return $this->foreignPrimaryKey;
		} elseif (!$this->isForeign && isset($this->localPrimaryKey)) {
			return $this->localPrimaryKey;
		}
		if (isset($this->companionObject)) {
			return $this->companionObject->primaryKey;
		}
		return null;
	}

	public function setPairedDataItem(DataItem $value)
	{
		$this->_pairedDataItem = $value;
		if (!isset($this->_localObject) && isset($value->localObject)) {
			$this->localObject = $value->localObject;
		}

		if (!isset($this->_foreignObject) && isset($value->foreignObject)) {
			$this->foreignObject = $value->foreignObject;
		}

		if ($value->handledDataItem) {
			$this->handledDataItem = $value->handledDataItem;
		}
	}

	public function getPairedDataItem()
	{
		return $this->_pairedDataItem;
	}

	public function setHandledDataItem($value)
	{
		if (isset($this->_pairedDataItem)) {
			$this->pairedDataItem->handledDataItem = $value;
		}
		if (!$this->_handledDataItem && $value) {
			$this->dataSource->reduceRemaining($this);
		}
		$this->clean();
		return $this->_handledDataItem = $value;
	}

	public function getHandledDataItem()
	{
		return $this->_handledDataItem;
	}

	public function getForeignObject()
	{
		if (is_null($this->_foreignObject)) {
			$this->trigger(self::EVENT_LOAD_FOREIGN_OBJECT);
		}
		return $this->_foreignObject;
	}

	public function setForeignObject($value)
	{
		$this->_foreignObject = $value;
	}

	public function getLocalObject()
	{
		if (is_null($this->_localObject)) {
			$this->trigger(self::EVENT_LOAD_LOCAL_OBJECT);
		}
		return $this->_localObject;
	}

	public function setLocalObject($value)
	{
		$this->_localObject = $value;
	}

	public function getAction()
	{
		return $this->dataSource->action;
	}

	public function getModule()
	{
		return $this->dataSource->module;
	}
}