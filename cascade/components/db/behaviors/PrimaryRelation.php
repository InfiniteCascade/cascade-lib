<?php
namespace cascade\components\db\behaviors;

use cascade\components\types\Relationship;

class PrimaryRelation extends \infinite\db\behaviors\PrimaryRelation
{
	public $registryClass = 'cascade\\models\\Registry';

	protected $_relationship;

	public function handlePrimary()
	{
		if (!parent::handlePrimary()) {
			return false;
		}
		if (empty($this->relationship)) {
			return false;
		}
		return $this->relationship->handlePrimary;
	}

	public function getRelationship()
	{
		if (is_null($this->_relationship)) {
			$parentObject = $this->owner->parentObject;
			$childObject= $this->owner->childObject;
			if ($parentObject && $childObject) {
				$this->_relationship = Relationship::getOne($parentObject->objectTypeItem, $childObject->objectTypeItem);
			}
		}
		return $this->_relationship;
	}

	public function setRelationship(Relationship $value)
	{
		$this->_relationship = $value;
	}
}