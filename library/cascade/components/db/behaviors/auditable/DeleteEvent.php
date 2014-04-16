<?php
namespace cascade\components\db\behaviors\auditable;

use Yii;

class DeleteEvent extends \infinite\db\behaviors\auditable\DeleteEvent
{
	public $objectType;

	public function setDirectObject($object)
	{
		parent::setDirectObject($object);
		if ($object->objectType) {
			$this->objectType = $object->objectType->systemId;
		}
	}
}
?>