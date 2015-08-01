<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * DataInterfaceEventBehavior [[@doctodo class_description:cascade\components\db\behaviors\auditable\DataInterfaceEventBehavior]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataInterfaceEventBehavior extends \canis\db\behaviors\ActiveRecord
{
    /**
     * @var [[@doctodo var_type:_dataInterface]] [[@doctodo var_description:_dataInterface]]
     */
    protected $_dataInterface;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \canis\db\behaviors\auditable\Event::EVENT_BEFORE_MODEL_SAVE => 'beforeModelSave',
        ];
    }

    /**
     * [[@doctodo method_description:beforeModelSave]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]]
     */
    public function beforeModelSave($event)
    {
        if (isset($this->dataInterface)) {
            $dataInterface = $this->dataInterface;
            if (is_object($dataInterface)) {
                $dataInterface = $dataInterface->primaryKey;
            }
            $event->model->data_interface_id = $dataInterface;
        }
    }

    /**
     * Set data interface.
     *
     * @param [[@doctodo param_type:object]] $object [[@doctodo param_description:object]]
     */
    public function setDataInterface($object)
    {
        $this->_dataInterface = $object;
    }

    /**
     * Get data interface.
     *
     * @return [[@doctodo return_type:getDataInterface]] [[@doctodo return_description:getDataInterface]]
     */
    public function getDataInterface()
    {
        return $this->_dataInterface;
    }
}
