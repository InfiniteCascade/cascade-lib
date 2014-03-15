<?php
namespace cascade\components\db\behaviors;

use infinite\helpers\ArrayHelper;

class Ownable extends \infinite\db\behaviors\Ownable
{
    public function determineOwner()
    {
    	if (!empty($this->owner->objectType)) {
    		return $this->owner->objectType->determineOwner($this->owner);
    	}
    	return false;
    }
}
?>