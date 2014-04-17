<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web;

/**
 * ObjectViewEvent [@doctodo write class description for ObjectViewEvent]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class ObjectViewEvent extends \yii\base\Event
{
    /**
     * @var __var_action_type__ __var_action_description__
     */
    public $action;
    /**
     * @var __var_accessed_type__ __var_accessed_description__
     */
    public $accessed = true;
    protected $_object;
    /**
     * @var __var__objectType_type__ __var__objectType_description__
     */
    /**
     * @var __var__object_type__ __var__object_description__
     */
    /**
     * @var __var__object_type__ __var__object_description__
     */
    /**
     * @var __var__object_type__ __var__object_description__
     */
    /**
     * @var __var__object_type__ __var__object_description__
     */
    protected $_objectType;

    /**
     * __method_handleWith_description__
     * @param  __param_callable_type__    $callable __param_callable_description__
     * @param  boolean                    $always   __param_always_description__ [optional]
     * @return __return_handleWith_type__ __return_handleWith_description__
     */
    public function handleWith($callable, $always = false)
    {
        if ($this->handled && !$always) { return false; }
        if (!is_callable($callable)) { return false; }
        call_user_func($callable, $this);

        return false;
    }

    /**
     * __method_setObject_description__
     * @param __param_object_type__ $object __param_object_description__
     */
    public function setObject($object)
    {
        if (is_null($this->_objectType)) {
            $this->objectType = $object->objectType;
        }
        $this->_object = $object;
    }

    /**
     * __method_getObject_description__
     * @return __return_getObject_type__ __return_getObject_description__
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * __method_setObjectType_description__
     * @param __param_type_type__ $type __param_type_description__
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
     * __method_getObjectType_description__
     * @return __return_getObjectType_type__ __return_getObjectType_description__
     */
    public function getObjectType()
    {
        return $this->_objectType;
    }
}
