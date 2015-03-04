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
     */
    protected $_object;
    /**
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
     */
    public function startRequest()
    {
        if (isset($_GET['p'])) {
            $this->_previousObject = Registry::getObject($_GET['p']);
        }
    }

    /**
     * Set object.
     */
    public function setObject($object)
    {
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
     * Get previous object.
     */
    public function getPreviousObject()
    {
        return $this->_previousObject;
    }
}
