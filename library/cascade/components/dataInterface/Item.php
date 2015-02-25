<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use infinite\base\exceptions\Exception;
use cascade\models\DataInterface;

/**
 * Item [@doctodo write class description for Item]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \infinite\base\collector\Item
{
    /**
     * @var __var_error_type__ __var_error_description__
     */
    public $error;

    /**
     * @var __var__name_type__ __var__name_description__
     */
    private $_name;
    /**
     * @var __var__module_type__ __var__module_description__
     */
    private $_module;
    /**
     * @var __var__checked_type__ __var__checked_description__
     */
    private $_checked;
    /**
     * @var __var__interfaceObject_type__ __var__interfaceObject_description__
     */
    protected $_interfaceObject;
    /**
     * @var __var__currentInterfaceAction_type__ __var__currentInterfaceAction_description__
     */
    protected $_currentInterfaceAction;

    /**
     * __method_run_description__
     * @return __return_run_type__ __return_run_description__
     */
    public function run($resumeLog = null, $action = null)
    {
        register_shutdown_function([$this, 'saveLog']);
        if ($action === null) {
            $action = new NonInteractiveAction();
        }

        $action->interface = $this;
        if (!empty($resumeLog)) {
            $action->resumeLog($resumeLog);
        }

        $this->_currentInterfaceAction = $action;
        if (!$this->_currentInterfaceAction->start()) {
            $this->error = 'Could not start interface action!';
            return false;
        }
        try {
            $this->object->run($this->_currentInterfaceAction);
        } catch (Exception $e) {
            $this->_currentInterfaceAction->status->addError('Exception raised: '. $e->getMessage());
            $this->_currentInterfaceAction->end(true);
            $this->error = 'Exception raised while running action ('. $e->getMessage() .').';

            return false;
        }
        $this->_currentInterfaceAction->end();

        return !$this->_currentInterfaceAction->status->hasError;
    }

    /**
     * __method_saveLog_description__
     * @return __return_saveLog_type__ __return_saveLog_description__
     */
    public function saveLog()
    {
        if (isset($this->_currentInterfaceAction)) {
            $this->_currentInterfaceAction->end(true);
        }
        return true;
    }

    /**
     * Get interface object
     * @return __return_getInterfaceObject_type__ __return_getInterfaceObject_description__
     * @throws Exception __exception_Exception_description__
     */
    public function getInterfaceObject()
    {
        if (is_null($this->_interfaceObject)) {
            $this->_interfaceObject = DataInterface::find()->where(['system_id' => $this->object->systemId])->one();
            if (empty($this->_interfaceObject)) {
                $this->_interfaceObject = new DataInterface;
                $this->_interfaceObject->name = $this->object->name;
                $this->_interfaceObject->system_id = $this->object->systemId;
                if (!$this->_interfaceObject->save()) {
                    var_dump($this->_interfaceObject->errors);
                    throw new Exception("Unable to save interface object!");
                }
            }
        }
        return $this->_interfaceObject;
    }

}
