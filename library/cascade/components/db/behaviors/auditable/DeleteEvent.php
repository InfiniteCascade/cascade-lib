<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

class DeleteEvent extends \infinite\db\behaviors\auditable\DeleteEvent
{
    public $objectType;

    public function setDirectObject($object)
    {
        parent::setDirectObject($object);
        if ($object->objectType) {
            $this->objectType = $object->objectType->systemId;
        }
    }
}
