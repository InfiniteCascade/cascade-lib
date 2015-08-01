<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * Auditable [[@doctodo class_description:cascade\components\db\behaviors\auditable\Auditable]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Auditable extends \canis\db\behaviors\auditable\Auditable
{
    /**
     * @var [[@doctodo var_type:_auditDataInterface]] [[@doctodo var_description:_auditDataInterface]]
     */
    protected $_auditDataInterface;
    /**
     * @inheritdoc
     */
    public $deleteEventClass = 'cascade\components\db\behaviors\auditable\DeleteEvent';
    /**
     * @inheritdoc
     */
    public $createEventClass = 'cascade\components\db\behaviors\auditable\CreateEvent';
    /**
     * @inheritdoc
     */
    public $updateEventClass = 'cascade\components\db\behaviors\auditable\UpdateEvent';

    /**
     * @inheritdoc
     */
    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['auditDataInterface']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'cascade\components\db\behaviors\auditable\DataInterfaceEventBehavior',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function prepareEventObject($event)
    {
        parent::prepareEventObject($event);
        $event->dataInterface = $this->auditDataInterface;

        return $event;
    }

    /**
     * Get indirect object.
     *
     * @return [[@doctodo return_type:getIndirectObject]] [[@doctodo return_description:getIndirectObject]]
     */
    public function getIndirectObject()
    {
        if (is_null($this->_indirectObject)) {

            // $this->indirectObject = $this->owner;
        }

        return $this->_indirectObject;
    }

    /**
     * Set audit data interface.
     *
     * @param [[@doctodo param_type:object]] $object [[@doctodo param_description:object]]
     */
    public function setAuditDataInterface($object)
    {
        $this->_auditDataInterface = $object;
    }

    /**
     * Get audit data interface.
     *
     * @return [[@doctodo return_type:getAuditDataInterface]] [[@doctodo return_description:getAuditDataInterface]]
     */
    public function getAuditDataInterface()
    {
        return $this->_auditDataInterface;
    }
}
