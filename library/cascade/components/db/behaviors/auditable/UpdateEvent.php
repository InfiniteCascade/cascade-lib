<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * UpdateEvent [[@doctodo class_description:cascade\components\db\behaviors\auditable\UpdateEvent]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class UpdateEvent extends \teal\db\behaviors\auditable\UpdateEvent
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
