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
class DeleteEvent extends \infinite\db\behaviors\auditable\DeleteEvent
{
    /**
     * @var __var_objectType_type__ __var_objectType_description__
     */
    public $objectType;

    /**
    * @inheritdoc
     */
    public function setDirectObject($object)
    {
        parent::setDirectObject($object);
        if ($object->objectType) {
            $this->objectType = $object->objectType->systemId;
        }
    }
}
