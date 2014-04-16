<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use cascade\models\DataInterfaceLog;
use yii\helpers\Console;

class Action extends \infinite\base\Object
{
    protected $_interface;
    protected $_status;
    protected $_log;
    protected $_id;
    protected $_settings = [];
    protected $_registry = [];

    public $progressPrefix = 'Loading';
    protected $_progress = false;
    protected $_progressTotal = 1;
    protected $_progressRemaining;
    protected $_progressPercentage;

    public function __construct(Item $interface = null, $resumeLog = null)
    {
        $this->_interface = $interface;
        if (!is_null($resumeLog)) {
            $this->_log = $resumeLog;
        }
    }

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

    public function setProgressTotal($total)
    {
        $this->_progressTotal = $total;
        $this->progress();
    }

    public function getProgressTotal()
    {
        return $this->_progressTotal;
    }

    public function getProgressDone()
    {
        return $this->progressTotal - $this->progressRemaining;
    }

    public function getProgressRemaining()
    {
        if (is_null($this->_progressRemaining)) {
            $this->_progressRemaining = $this->progressTotal;
        }

        return $this->_progressRemaining;
    }

    public function reduceRemaining($n)
    {
        $this->_progressRemaining = $this->progressRemaining - $n;

        return $this->progress();
    }

    public function setSettings($value)
    {
        $this->_settings = $value;
    }

    public function getSettings()
    {
        return $this->_settings;
    }

    public function start()
    {
        return $this->save() && $this->_id = $this->log->id;
    }

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

    public function getStatus()
    {
        if (!isset($this->_status)) {
            $this->_status = new Status($this);
        }

        return $this->_status;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function addRegistry($key, $objectId)
    {
        $this->_registry[$key] = $objectId;
    }

    public function getRegistry()
    {
        return $this->_registry;
    }

    public function objectInRegistry($objectId)
    {
        return in_array($objectId, $this->_registry);
    }

    public function keyInRegistry($keyId)
    {
        return isset($this->_registry[$keyId]);
    }
}
