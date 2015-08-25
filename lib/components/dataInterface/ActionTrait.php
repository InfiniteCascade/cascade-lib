<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface;

use cascade\models\DataInterfaceLog;
use Yii;

/**
 * @author Jacob Morrison <email@ofjacob.com>
 */
trait ActionTrait
{
    /**
     */
    protected $_interface;
    /**
     */
    protected $_status;
    /**
     */
    protected $_log;
    /**
     */
    protected $_registry = [];

    public function __sleep()
    {
        if (is_object($this->_interface)) {
            $this->_interface = $this->_interface->systemId;
        }

        return parent::__sleep();
    }

    public function setInterface($interface)
    {
        $this->_interface = $interface;
    }

    public function getInterface()
    {
        if (!empty($this->_interface)
            && is_string($this->_interface)
            && Yii::$app->collectors['dataInterfaces']->has($this->_interface)
            ) {
            $this->_interface = Yii::$app->collectors['dataInterfaces']->getOne($this->_interface);
        }
        return $this->_interface;
    }

    public function resumeLog($log)
    {
        $this->_log = $log;
        $this->_log->status = 'running';
        $this->_log->started = date("Y-m-d G:i:s");
        $this->_log->peak_memory = memory_get_usage();
        $this->_log->save();
    }

    /**
     *
     */
    public function start()
    {
        return $this->save() && $this->_id = $this->log->id;
    }

    /**
     *
     */
    public function end($endInterrupted = false)
    {
        return $this->log->end($endInterrupted);
    }

    /**
     *
     */
    public function save()
    {
        parent::save();

        $this->log->message = serialize($this->status);
        $newPeak = memory_get_usage();
        if ($newPeak > $this->log->peak_memory) {
            $this->log->peak_memory = $newPeak;
        }
        if (empty($this->interface)) {
            return true;
        }

        return $this->log->save();
    }

    /**
     * Get log.
     */
    public function getLog()
    {
        if (!isset($this->_log)) {
            $this->_log = new DataInterfaceLog();
            if (!empty($this->interface)) {
                $this->_log->data_interface_id = $this->interface->interfaceObject->id;
            }
            $this->_log->status = 'running';
            $this->_log->started = date("Y-m-d G:i:s");
            $this->_log->peak_memory = memory_get_usage();
        }

        return $this->_log;
    }

    /**
     * Get status.
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
     * Get id.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     *
     */
    public function addRegistry($key, $objectId)
    {
        $this->_registry[$key] = $objectId;
    }

    /**
     * Get registry.
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     *
     */
    public function objectInRegistry($objectId)
    {
        return in_array($objectId, $this->_registry);
    }

    /**
     *
     */
    public function keyInRegistry($keyId)
    {
        return isset($this->_registry[$keyId]);
    }
}
