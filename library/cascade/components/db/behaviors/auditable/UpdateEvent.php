<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * DeleteEvent [@doctodo write class description for DeleteEvent]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class UpdateEvent extends \infinite\db\behaviors\auditable\UpdateEvent
{
	public function getVerb()
    {
        
        if (isset($this->directObject->objectType) && $this->directObject->objectType->getUpdateVerb($this->directObject) !== null) {
            return $this->directObject->objectType->getUpdateVerb($this->directObject);
        }
        return parent::getVerb();
    }
}
