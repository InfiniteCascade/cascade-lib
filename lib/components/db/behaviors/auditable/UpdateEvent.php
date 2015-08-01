<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * UpdateEvent [[@doctodo class_description:cascade\components\db\behaviors\auditable\UpdateEvent]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class UpdateEvent extends \canis\db\behaviors\auditable\UpdateEvent
{
    /**
     * @inheritdoc
     */
    public function getVerb()
    {
        if (isset($this->directObject->objectType) && $this->directObject->objectType->getUpdateVerb($this->directObject) !== null) {
            return $this->directObject->objectType->getUpdateVerb($this->directObject);
        }

        return parent::getVerb();
    }
}
