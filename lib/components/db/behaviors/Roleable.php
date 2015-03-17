<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
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
