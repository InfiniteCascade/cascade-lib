<?php
namespace cascade\components\db\behaviors;

use infinite\helpers\ArrayHelper;

class Roleable extends \infinite\db\behaviors\Roleable
{
    public function determineAccessLevel($object, $role)
    {
        
    	return false;
    }
}
?>