<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface;

use cascade\models\DataInterface;
use canis\base\exceptions\Exception;

/**
 * Item [[@doctodo class_description:cascade\components\dataInterface\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \canis\base\collector\Item
{
    /**
     * @var [[@doctodo var_type:error]] [[@doctodo var_description:error]]
     */
    public $error;

    /**
     * @var [[@doctodo var_type:_name]] [[@doctodo var_description:_name]]
     */
    private $_name;
    /**
     * @var [[@doctodo var_type:_module]] [[@doctodo var_description:_module]]
     */
    private $_module;
    /**
     * @var [[@doctodo var_type:_checked]] [[@doctodo var_description:_checked]]
     */
    private $_checked;
    /**
     * @var [[@doctodo var_type:_interfaceObject]] [[@doctodo var_description:_interfaceObject]]
     */
    protected $_interfaceObject;
    /**
     * @var [[@doctodo var_type:_currentInterfaceAction]] [[@doctodo var_description:_currentInterfaceAction]]
     */
    protected $_currentInterfaceAction;

    /**
     * [[@doctodo method_description:run]].
     *
     * @param [[@doctodo param_type:resumeLog]] $resumeLog [[@doctodo param_description:resumeLog]] [optional]
     * @param [[@doctodo param_type:action]]    $action    [[@doctodo param_description:action]] [optional]
     *
     * @return [[@doctodo return_type:run]] [[@doctodo return_description:run]]
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
            $this->_currentInterfaceAction->status->addError('Exception raised: ' . $e->getMessage());
            $this->_currentInterfaceAction->end(true);
            $this->error = 'Exception raised while running action (' . $e->getMessage() . ').';

            return false;
        }
        $this->_currentInterfaceAction->end();

        return !$this->_currentInterfaceAction->status->hasError;
    }

    /**
     * [[@doctodo method_description:saveLog]].
     *
     * @return [[@doctodo return_type:saveLog]] [[@doctodo return_description:saveLog]]
     */
    public function saveLog()
    {
        if (isset($this->_currentInterfaceAction)) {
            $this->_currentInterfaceAction->end(true);
        }

        return true;
    }

    /**
     * Get interface object.
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:getInterfaceObject]] [[@doctodo return_description:getInterfaceObject]]
     *
     */
    public function getInterfaceObject()
    {
        if (is_null($this->_interfaceObject)) {
            $this->_interfaceObject = DataInterface::find()->where(['system_id' => $this->object->systemId])->one();
            if (empty($this->_interfaceObject)) {
                $this->_interfaceObject = new DataInterface();
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
