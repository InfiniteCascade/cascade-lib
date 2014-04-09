<?php
namespace cascade\components\db\behaviors;

use yii\helpers\Url;

class SearchTermResult extends \infinite\db\behaviors\SearchTermResult
{
	protected $_icon;
	protected $_objectType;
	protected $_objectTypeDescriptor;
	protected $_url;


	public function setUrl($value)
	{
		$this->_url = $value;
	}

	public function getUrl()
	{
		if (is_null($this->_url) && isset($this->object)) {
			$this->_url = Url::to($this->object->getUrl('view', [], false));
		}
		return $this->_url;
	}

	public function getIcon()
	{
		if (is_null($this->_icon) && isset($this->object)) {
			$this->_icon = ['class' => $this->object->objectType->icon, 'title' => $this->objectTypeDescriptor];
		}
		return $this->_icon;
	}

	public function setIcon($icon) 
	{
		if (is_string($icon)) {
			$icon = ['class' => $icon];
		}
		$this->_icon = $icon;
	}

	public function getObjectTypeDescriptor()
	{
		if (is_null($this->_objectTypeDescriptor) && isset($this->object)) {
			$this->_objectTypeDescriptor = $this->object->objectType->title->upperSingular;
		}
		return $this->_objectTypeDescriptor;
	}

	public function setObjectTypeDescriptor($type) 
	{
		$this->_objectTypeDescriptor = $type;
	}


	public function getObjectType()
	{
		if (is_null($this->_objectType) && isset($this->object)) {
			$this->_objectType = $this->object->objectType->systemId;
		}
		return $this->_objectType;
	}

	public function setObjectType($type) 
	{
		$this->_objectType = $type;
	}


	public function toArray()
	{
		return array_merge(parent::toArray(), [
			'icon' => $this->icon,
			'url' => $this->url,
			'objectType' => $this->objectType
		]);
	}

	public function getScore()
	{
		if (empty($this->object->objectType->searchWeight)) {
			return 0;
		}
		return parent::getScore() * $this->object->objectType->searchWeight;
	}
}
?>