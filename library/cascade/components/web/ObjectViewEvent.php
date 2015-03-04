<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web;

/**
 * ObjectViewEvent [@doctodo write class description for ObjectViewEvent].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ObjectViewEvent extends \yii\base\Event
{
    /**
     */
    public $action;
    /**
     */
    public $accessed = true;
    /**
     */
    protected $_object;
    /**
     */
    protected $_objectType;

    /**
     *
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
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Set object type.
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
     */
    public function getObjectType()
    {
        return $this->_objectType;
    }
}
