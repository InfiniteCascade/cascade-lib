<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * Auditable [@doctodo write class description for Auditable].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Auditable extends \infinite\db\behaviors\auditable\Auditable
{
    protected $_auditDataInterface;
    /**
     * @inheritdoc
     */
    public $deleteEventClass = 'cascade\components\db\behaviors\auditable\DeleteEvent';
    public $createEventClass = 'cascade\components\db\behaviors\auditable\CreateEvent';
    public $updateEventClass = 'cascade\components\db\behaviors\auditable\UpdateEvent';

    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['auditDataInterface']);
    }

    public function behaviors()
    {
        return [
            [
                'class' => 'cascade\components\db\behaviors\auditable\DataInterfaceEventBehavior',
            ],
        ];
    }

    public function prepareEventObject($event)
    {
        parent::prepareEventObject($event);
        $event->dataInterface = $this->auditDataInterface;

        return $event;
    }

    /**
     * Get indirect object.
     */
    public function getIndirectObject()
    {
        if (is_null($this->_indirectObject)) {

            // $this->indirectObject = $this->owner;
        }

        return $this->_indirectObject;
    }

    public function setAuditDataInterface($object)
    {
        $this->_auditDataInterface = $object;
    }

    public function getAuditDataInterface()
    {
        return $this->_auditDataInterface;
    }
}
