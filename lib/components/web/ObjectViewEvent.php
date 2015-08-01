<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web;

/**
 * ObjectViewEvent [[@doctodo class_description:cascade\components\web\ObjectViewEvent]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ObjectViewEvent extends \yii\base\Event
{
    /**
     * @var [[@doctodo var_type:action]] [[@doctodo var_description:action]]
     */
    public $action;
    /**
     * @var [[@doctodo var_type:accessed]] [[@doctodo var_description:accessed]]
     */
    public $accessed = true;
    /**
     * @var [[@doctodo var_type:_object]] [[@doctodo var_description:_object]]
     */
    protected $_object;
    /**
     * @var [[@doctodo var_type:_objectType]] [[@doctodo var_description:_objectType]]
     */
    protected $_objectType;

    /**
     * [[@doctodo method_description:handleWith]].
     *
     * @param [[@doctodo param_type:callable]] $callable [[@doctodo param_description:callable]]
     * @param boolean                          $always   [[@doctodo param_description:always]] [optional]
     *
     * @return [[@doctodo return_type:handleWith]] [[@doctodo return_description:handleWith]]
     */
    public function handleWith($callable, $always = false)
    {
        if ($this->handled && !$always) {
            return false;
        }
        if (!is_callable($callable)) {
            return false;
        }
        call_user_func($callable, $this);

        return false;
    }

    /**
     * Set object.
     *
     * @param [[@doctodo param_type:object]] $object [[@doctodo param_description:object]]
     */
    public function setObject($object)
    {
        if (is_null($this->_objectType)) {
            $this->objectType = $object->objectType;
        }
        $this->_object = $object;
    }

    /**
     * Get object.
     *
     * @return [[@doctodo return_type:getObject]] [[@doctodo return_description:getObject]]
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Set object type.
     *
     * @param [[@doctodo param_type:type]] $type [[@doctodo param_description:type]]
     */
    public function setObjectType($type)
    {
        if (!is_object($type)) {
            if (Yii::$app->collectors['types']->has($type)) {
                $type = Yii::$app->collectors['types']->getOne($type)->object;
            } else {
                $type = null;
            }
        }
        $this->_objectType = $type;
    }

    /**
     * Get object type.
     *
     * @return [[@doctodo return_type:getObjectType]] [[@doctodo return_description:getObjectType]]
     */
    public function getObjectType()
    {
        return $this->_objectType;
    }
}
