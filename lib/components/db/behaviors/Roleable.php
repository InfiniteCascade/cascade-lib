<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors;

/**
 * Roleable [[@doctodo class_description:cascade\components\db\behaviors\Roleable]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Roleable extends \canis\db\behaviors\Roleable
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
