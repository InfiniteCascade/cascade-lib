<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db\behaviors;

/**
 * Roleable [[@doctodo class_description:cascade\components\db\behaviors\Roleable]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Roleable extends \teal\db\behaviors\Roleable
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
