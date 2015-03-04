<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web;

use cascade\models\Registry;
use Yii;
use yii\web\Application;

/**
 * Request [@doctodo write class description for Request].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Request extends \infinite\web\Request
{
    /**
     * @var __var__object_type__ __var__object_description__
     */
    protected $_object;
    /**
     * @var __var__previousObject_type__ __var__previousObject_description__
     */
    protected $_previousObject;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'startRequest']);
    }

    /**
     * __method_startRequest_description__.
     */
    public function startRequest()
    {
        if (isset($_GET['p'])) {
            $this->_previousObject = Registry::getObject($_GET['p']);
        }
    }

    /**
     * Set object.
     *
     * @param __param_object_type__ $object __param_object_description__
     */
    public function setObject($object)
    {
        $this->_object = $object;
    }

    /**
     * Get object.
     *
     * @return __return_getObject_type__ __return_getObject_description__
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Get previous object.
     *
     * @return __return_getPreviousObject_type__ __return_getPreviousObject_description__
     */
    public function getPreviousObject()
    {
        return $this->_previousObject;
    }
}
