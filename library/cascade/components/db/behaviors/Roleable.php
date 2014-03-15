<?php
namespace cascade\components\db\behaviors;

use infinite\helpers\ArrayHelper;

class Roleable extends \infinite\db\behaviors\Roleable
{
    public function determineAccessLevel($role, $aro = null)
    {
    	if (!empty($this->owner->objectType)) {
    		return $this->owner->objectType->determineAccessLevel($this->owner, $role, $aro);
    	}
    	return false;
    }
}
?>