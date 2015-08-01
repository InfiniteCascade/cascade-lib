<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors;

/**
 * Ownable [[@doctodo class_description:cascade\components\db\behaviors\Ownable]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Ownable extends \canis\db\behaviors\Ownable
{
    /**
     * @inheritdoc
     */
    public function determineOwner()
    {
        if (!empty($this->owner->objectType)) {
            return $this->owner->objectType->determineOwner($this->owner);
        }

        return false;
    }
}
