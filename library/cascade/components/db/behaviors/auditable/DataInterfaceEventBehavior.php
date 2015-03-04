<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * ActiveRecord is the model class for table "{{%active_record}}".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataInterfaceEventBehavior extends \infinite\db\behaviors\ActiveRecord
{
    protected $_dataInterface;

    public function events()
    {
        return [
            \infinite\db\behaviors\auditable\Event::EVENT_BEFORE_MODEL_SAVE => 'beforeModelSave',
        ];
    }

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

    public function setDataInterface($object)
    {
        $this->_dataInterface = $object;
    }

    public function getDataInterface()
    {
        return $this->_dataInterface;
    }
}
