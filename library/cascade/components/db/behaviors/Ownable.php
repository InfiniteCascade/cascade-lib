<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

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
