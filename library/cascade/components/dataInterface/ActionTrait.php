<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use Yii;
use cascade\models\DataInterfaceLog;
use infinite\helpers\Console;

/**
 * Action [@doctodo write class description for Action]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
trait ActionTrait
{
    /**
     * @var __var__interface_type__ __var__interface_description__
     */
    protected $_interface;
    /**
     * @var __var__status_type__ __var__status_description__
     */
    protected $_status;
    /**
     * @var __var__log_type__ __var__log_description__
     */
    protected $_log;
    /**
     * @var __var__registry_type__ __var__registry_description__
     */
    protected $_registry = [];


    public function setInterface($interface)
    {
        $this->_interface = $interface;
    }

    public function resumeLog($log)
    {

        $this->_log = $resumeLog;
        $this->_log->status = 'running';
        $this->_log->started = date("Y-m-d G:i:s");
        $this->_log->peak_memory = memory_get_usage();
        $this->_log->save();
    }


    /**
     * __method_start_description__
     * @return __return_start_type__ __return_start_description__
     */
    public function start()
    {
        return $this->save() && $this->_id = $this->log->id;
    }

    /**
     * __method_end_description__
     * @param boolean             $endInterrupted __param_endInterrupted_description__ [optional]
     * @return __return_end_type__ __return_end_description__
     */
    public function end($endInterrupted = false)
    {
        return $this->log->end($endInterrupted);
    }

    /**
     * __method_save_description__
     * @return __return_save_type__ __return_save_description__
     */
    public function save()
    {
        $this->log->message = serialize($this->status);
        $newPeak = memory_get_usage();
        if ($newPeak > $this->log->peak_memory) {
            $this->log->peak_memory = $newPeak;
        }
        if (empty($this->_interface)) {
            return true;
        }

        return $this->log->save();
    }

    /**
     * Get log
     * @return __return_getLog_type__ __return_getLog_description__
     */
    public function getLog()
    {
        if (!isset($this->_log)) {
            $this->_log = new DataInterfaceLog;
            if (!empty($this->_interface)) {
                $this->_log->data_interface_id = $this->_interface->interfaceObject->id;
            }
            $this->_log->status = 'running';
            $this->_log->started = date("Y-m-d G:i:s");
            $this->_log->peak_memory = memory_get_usage();
        }

        return $this->_log;
    }

    /**
     * Get status
     * @return __return_getStatus_type__ __return_getStatus_description__
     */
    public function getStatus()
    {
        if (isset($this->_log)) {
            $this->_status = $this->log->statusLog;
        } elseif (!isset($this->_status)) {
            $this->_status = new Status($this->log);
        }


        return $this->_status;
    }

    /**
     * Get id
     * @return __return_getId_type__ __return_getId_description__
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * __method_addRegistry_description__
     * @param __param_key_type__      $key      __param_key_description__
     * @param __param_objectId_type__ $objectId __param_objectId_description__
     */
    public function addRegistry($key, $objectId)
    {
        $this->_registry[$key] = $objectId;
    }

    /**
     * Get registry
     * @return __return_getRegistry_type__ __return_getRegistry_description__
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * __method_objectInRegistry_description__
     * @param __param_objectId_type__          $objectId __param_objectId_description__
     * @return __return_objectInRegistry_type__ __return_objectInRegistry_description__
     */
    public function objectInRegistry($objectId)
    {
        return in_array($objectId, $this->_registry);
    }

    /**
     * __method_keyInRegistry_description__
     * @param __param_keyId_type__          $keyId __param_keyId_description__
     * @return __return_keyInRegistry_type__ __return_keyInRegistry_description__
     */
    public function keyInRegistry($keyId)
    {
        return isset($this->_registry[$keyId]);
    }
}
