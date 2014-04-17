<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use cascade\models\DataInterfaceLog;
use yii\helpers\Console;

/**
 * Action [@doctodo write class description for Action]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Action extends \infinite\base\Object
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
     * @var __var__id_type__ __var__id_description__
     */
    protected $_id;
    /**
     * @var __var__settings_type__ __var__settings_description__
     */
    protected $_settings = [];
    /**
     * @var __var__registry_type__ __var__registry_description__
     */
    protected $_registry = [];

    /**
     * @var __var_progressPrefix_type__ __var_progressPrefix_description__
     */
    public $progressPrefix = 'Loading';
    protected $_progress = false;
    /**
     * @var __var__progressTotal_type__ __var__progressTotal_description__
     */
    protected $_progressTotal = 1;
    /**
     * @var __var__progressRemaining_type__ __var__progressRemaining_description__
     */
    protected $_progressRemaining;
    /**
     * @var __var__progressPercentage_type__ __var__progressPercentage_description__
     */
    /**
     * @var __var__progress_type__ __var__progress_description__
     */
    /**
     * @var __var__progress_type__ __var__progress_description__
     */
    /**
     * @var __var__progress_type__ __var__progress_description__
     */
    /**
     * @var __var__progress_type__ __var__progress_description__
     */
    protected $_progressPercentage;

    /**
    * @inheritdoc
    **/
    public function __construct(Item $interface = null, $resumeLog = null)
    {
        $this->_interface = $interface;
        if (!is_null($resumeLog)) {
            $this->_log = $resumeLog;
        }
    }

    /**
     * __method_progress_description__
     * @return __return_progress_type__ __return_progress_description__
     */
    public function progress()
    {
        return;
        if (!$this->_progress) {
            $this->_progress = true;
            Console::startProgress($this->progressDone, $this->progressTotal, $this->progressPrefix . ' ');
        }
        $currentPercentage = (int) floor(($this->progressDone / $this->progressTotal) * 100);
        if ($this->_progressPercentage !== $currentPercentage) {
            $this->_progressPercentage = $currentPercentage;
            Console::updateProgress($this->progressDone, $this->progressTotal, $this->progressPrefix . ' ');
        }
    }

    /**
     * __method_setProgressTotal_description__
     * @param __param_total_type__ $total __param_total_description__
     */
    public function setProgressTotal($total)
    {
        $this->_progressTotal = $total;
        $this->progress();
    }

    /**
     * __method_getProgressTotal_description__
     * @return __return_getProgressTotal_type__ __return_getProgressTotal_description__
     */
    public function getProgressTotal()
    {
        return $this->_progressTotal;
    }

    /**
     * __method_getProgressDone_description__
     * @return __return_getProgressDone_type__ __return_getProgressDone_description__
     */
    public function getProgressDone()
    {
        return $this->progressTotal - $this->progressRemaining;
    }

    /**
     * __method_getProgressRemaining_description__
     * @return __return_getProgressRemaining_type__ __return_getProgressRemaining_description__
     */
    public function getProgressRemaining()
    {
        if (is_null($this->_progressRemaining)) {
            $this->_progressRemaining = $this->progressTotal;
        }

        return $this->_progressRemaining;
    }

    /**
     * __method_reduceRemaining_description__
     * @param  __param_n_type__                $n __param_n_description__
     * @return __return_reduceRemaining_type__ __return_reduceRemaining_description__
     */
    public function reduceRemaining($n)
    {
        $this->_progressRemaining = $this->progressRemaining - $n;

        return $this->progress();
    }

    /**
     * __method_setSettings_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setSettings($value)
    {
        $this->_settings = $value;
    }

    /**
     * __method_getSettings_description__
     * @return __return_getSettings_type__ __return_getSettings_description__
     */
    public function getSettings()
    {
        return $this->_settings;
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
     * @param  boolean             $endInterrupted __param_endInterrupted_description__ [optional]
     * @return __return_end_type__ __return_end_description__
     */
    public function end($endInterrupted = false)
    {
        if (!is_null($this->log->ended)) { return true; }
        Console::endProgress(true);
        Console::stdout("Done! ". PHP_EOL);
        if ($endInterrupted) {
            $lerror = error_get_last();
            if (!empty($lerror)) {
                $this->status->addError("{$lerror['file']}:{$lerror['line']} {$lerror['message']}");
                Console::stdout(PHP_EOL . PHP_EOL . "{$lerror['file']}:{$lerror['line']} {$lerror['message']}" . PHP_EOL);
            }
            $this->log->status = 'interrupted';
        } elseif ($this->status->error) {
            $this->log->status = 'failed';
        } else {
            $this->log->status = 'success';
        }
        $this->log->ended = date("Y-m-d G:i:s");

        return $this->save();
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
     * __method_getLog_description__
     * @return __return_getLog_type__ __return_getLog_description__
     */
    public function getLog()
    {
        if (is_null($this->_log)) {
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
     * __method_getStatus_description__
     * @return __return_getStatus_type__ __return_getStatus_description__
     */
    public function getStatus()
    {
        if (!isset($this->_status)) {
            $this->_status = new Status($this);
        }

        return $this->_status;
    }

    /**
     * __method_getId_description__
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
     * __method_getRegistry_description__
     * @return __return_getRegistry_type__ __return_getRegistry_description__
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * __method_objectInRegistry_description__
     * @param  __param_objectId_type__          $objectId __param_objectId_description__
     * @return __return_objectInRegistry_type__ __return_objectInRegistry_description__
     */
    public function objectInRegistry($objectId)
    {
        return in_array($objectId, $this->_registry);
    }

    /**
     * __method_keyInRegistry_description__
     * @param  __param_keyId_type__          $keyId __param_keyId_description__
     * @return __return_keyInRegistry_type__ __return_keyInRegistry_description__
     */
    public function keyInRegistry($keyId)
    {
        return isset($this->_registry[$keyId]);
    }
}
