<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db\behaviors;

/**
 * Ownable [[@doctodo class_description:cascade\components\db\behaviors\Ownable]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Ownable extends \teal\db\behaviors\Ownable
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
