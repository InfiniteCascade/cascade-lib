<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * DeleteEvent [@doctodo write class description for DeleteEvent].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class CreateEvent extends \infinite\db\behaviors\auditable\CreateEvent
{
    public function getVerb()
    {
        if (isset($this->directObject->objectType) && !is_object($this->directObject->objectType)) {
            \d($this->directObject->objectType);
            \d(get_class($this->directObject));
            \d($this->model->id);
            exit;
        }
        if (isset($this->directObject->objectType) && $this->directObject->objectType->getInsertVerb($this->directObject) !== null) {
            return $this->directObject->objectType->getInsertVerb($this->directObject);
        }

        return parent::getVerb();
    }
}
