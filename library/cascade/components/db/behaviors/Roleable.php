<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

/**
 * Roleable [@doctodo write class description for Roleable]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Roleable extends \infinite\db\behaviors\Roleable
{
    /**
    * @inheritdoc
     */
    public function determineAccessLevel($role, $aro = null)
    {
        if (!empty($this->owner->objectType)) {
            return $this->owner->objectType->determineAccessLevel($this->owner, $role, $aro);
        }

        return false;
    }
}
