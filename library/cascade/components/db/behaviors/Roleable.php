<?php
namespace cascade\components\db\behaviors;

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
