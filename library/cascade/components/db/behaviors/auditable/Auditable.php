<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * Auditable [@doctodo write class description for Auditable]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Auditable extends \infinite\db\behaviors\auditable\Auditable
{
    /**
     * @inheritdoc
     */
    public $deleteEventClass = 'cascade\\components\\db\\behaviors\\auditable\\DeleteEvent';
    public $insertEventClass = 'cascade\\components\\db\\behaviors\\auditable\\InsertEvent';
    public $updateEventClass = 'cascade\\components\\db\\behaviors\\auditable\\UpdateEvent';

    /**
     * Get indirect object
     * @return __return_getIndirectObject_type__ __return_getIndirectObject_description__
     */
    public function getIndirectObject()
    {
        if (is_null($this->_indirectObject)) {
        	
            // $this->indirectObject = $this->owner;
        }
        return $this->_indirectObject;
    }

}
