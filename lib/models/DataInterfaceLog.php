<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use cascade\components\dataInterface\Status;
use canis\caching\Cacher;
use canis\helpers\Date;
use canis\helpers\StringHelper;
use yii\helpers\Url;

/**
 * DataInterfaceLog is the model class for table "data_interface_log".
 *
 * @property string $id
 * @property string $data_interface_id
 * @property string $status
 * @property string $message
 * @property integer $peak_memory
 * @property string $started
 * @property string $ended
 * @property string $created
 * @property string $modified
 * @property DataInterface $dataInterface
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataInterfaceLog extends \cascade\components\db\ActiveRecord
{
    /**
     * @var [[@doctodo var_type:_statusLog]] [[@doctodo var_description:_statusLog]]
     */
    protected $_statusLog;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'updateLastUpdated']);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'updatePeakMemory']);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'serializeStatusLog']);
    }

    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_interface_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data_interface_id'], 'required'],
            [['message'], 'string'],
            [['peak_memory'], 'integer'],
            [['started', 'ended', 'created', 'modified', 'last_update'], 'safe'],
            [['data_interface_id'], 'string', 'max' => 36],
            [['status'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_interface_id' => 'Data Interface ID',
            'status' => 'Status',
            'message' => 'Message',
            'peak_memory' => 'Peak Memory',
            'started' => 'Started',
            'ended' => 'Ended',
        ];
    }

    /**
     * Get data interface.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getDataInterface()
    {
        return $this->hasOne(DataInterface::className(), ['id' => 'data_interface_id']);
    }

    /**
     * [[@doctodo method_description:run]].
     *
     * @param [[@doctodo param_type:interactiveAction]] $interactiveAction [[@doctodo param_description:interactiveAction]] [optional]
     *
     * @return [[@doctodo return_type:run]] [[@doctodo return_description:run]]
     */
    public function run($interactiveAction = null)
    {
        if (empty($this->dataInterface) || !($dataInterfaceItem = $this->dataInterface->dataInterfaceItem)) {
            return false;
        }

        return $dataInterfaceItem->run($this, $interactiveAction);
    }

    /**
     * Get status log.
     *
     * @param boolean $checkRecent [[@doctodo param_description:checkRecent]] [optional]
     *
     * @return [[@doctodo return_type:getStatusLog]] [[@doctodo return_description:getStatusLog]]
     */
    public function getStatusLog($checkRecent = false)
    {
        if (!isset($this->_statusLog)) {
            $this->_statusLog = Cacher::get([get_called_class(), $this->primaryKey]);
            if (empty($this->_statusLog)) {
                if (is_null($this->message)) {
                    $this->_statusLog = $this->_startStatus();
                } else {
                    $this->_statusLog = unserialize($this->message);
                }
            } elseif ($checkRecent) {
                $testStatusLog = unserialize($this->message);
                if ($testStatusLog && $testStatusLog->lastUpdate > $this->_statusLog->lastUpdate) {
                    $this->_statusLog = $testStatusLog;
                }
            }
            if (empty($this->_statusLog)) {
                $this->_statusLog = new Status();
            }
        }
        $this->_statusLog->log = $this;

        return $this->_statusLog;
    }

    /**
     * [[@doctodo method_description:saveCache]].
     */
    public function saveCache()
    {
        $this->statusLog->lastUpdate = microtime(true);
        Cacher::set([get_called_class(), $this->primaryKey], $this->statusLog, 3600);
    }

    /**
     * [[@doctodo method_description:serializeStatusLog]].
     */
    public function serializeStatusLog()
    {
        $this->statusLog->lastUpdate = microtime(true);
        $this->message = serialize($this->statusLog);
    }

    /**
     * [[@doctodo method_description:updateStatus]].
     *
     * @param [[@doctodo param_type:newStatus]] $newStatus [[@doctodo param_description:newStatus]]
     *
     * @return [[@doctodo return_type:updateStatus]] [[@doctodo return_description:updateStatus]]
     */
    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;

        return $this->save();
    }

    /**
     * [[@doctodo method_description:_startStatus]].
     *
     * @return [[@doctodo return_type:_startStatus]] [[@doctodo return_description:_startStatus]]
     */
    protected function _startStatus()
    {
        $status = new Status($this);

        return $status;
    }

    /**
     * [[@doctodo method_description:start]].
     *
     * @return [[@doctodo return_type:start]] [[@doctodo return_description:start]]
     */
    public function start()
    {
        Cacher::set([get_called_class(), $this->primaryKey], false);

        return $this->save();
    }

    /**
     * [[@doctodo method_description:updatePeakMemory]].
     */
    public function updatePeakMemory()
    {
        if (is_null($this->peak_memory)
            || (int) $this->peak_memory < memory_get_peak_usage()) {
            $this->peak_memory = memory_get_peak_usage();
        }
    }

    /**
     * [[@doctodo method_description:updateLastUpdated]].
     */
    public function updateLastUpdated()
    {
        $this->last_update = date("Y-m-d G:i:s");
    }

    /**
     * [[@doctodo method_description:clearStatusLogCache]].
     */
    public function clearStatusLogCache()
    {
        Cacher::set([get_called_class(), $this->primaryKey], false, 3600);
    }

    /**
     * [[@doctodo method_description:end]].
     *
     * @param boolean $endInterrupted [[@doctodo param_description:endInterrupted]] [optional]
     * @param boolean $saveAlways     [[@doctodo param_description:saveAlways]] [optional]
     *
     * @return [[@doctodo return_type:end]] [[@doctodo return_description:end]]
     */
    public function end($endInterrupted = false, $saveAlways = true)
    {
        $this->getStatusLog(true);
        if (!is_null($this->ended)) {
            if ($saveAlways) {
                $this->save();
            }

            return true;
        }
        if ($endInterrupted) {
            $lerror = error_get_last();
            if (!empty($lerror)) {
                $this->statusLog->addError("{$lerror['file']}:{$lerror['line']} {$lerror['message']}");
            }
            $this->status = 'interrupted';
        } elseif ($this->statusLog->hasError) {
            $this->status = 'failed';
        } else {
            $this->status = 'success';
        }
        $this->ended = date("Y-m-d G:i:s");

        return $this->save();
    }

    /**
     * Get estimate time remaining.
     *
     * @return [[@doctodo return_type:getEstimateTimeRemaining]] [[@doctodo return_description:getEstimateTimeRemaining]]
     */
    public function getEstimateTimeRemaining()
    {
        $estimatedDuration = $this->dataInterface->estimateDuration();
        if ($estimatedDuration) {
            $startedTime = strtotime($this->started);
            $estimatedEndTime = $startedTime + $estimatedDuration;
            if (time() > $estimatedEndTime) {
                return false;
            }

            return $estimatedEndTime - time();
        }

        return false;
    }

    /**
     * Get duration.
     *
     * @return [[@doctodo return_type:getDuration]] [[@doctodo return_description:getDuration]]
     */
    public function getDuration()
    {
        $ended = microtime(true);
        if ($this->ended) {
            $ended = strtotime($this->ended);
        }
        $started = strtotime($this->started);

        return Date::niceDuration($ended-$started);
    }

    /**
     * Get is most recent.
     *
     * @return [[@doctodo return_type:getIsMostRecent]] [[@doctodo return_description:getIsMostRecent]]
     */
    public function getIsMostRecent()
    {
        return !empty($this->dataInterface) && $this->dataInterface->lastDataInterfaceLog && $this->dataInterface->lastDataInterfaceLog->primaryKey === $this->primaryKey;
    }

    /**
     * Get data package.
     *
     * @return [[@doctodo return_type:getDataPackage]] [[@doctodo return_description:getDataPackage]]
     */
    public function getDataPackage()
    {
        $p = [];
        $p['_'] = [];
        $p['_']['url'] = Url::to(['admin/interface/view-log', 'id' => $this->id, 'package' => 1]);
        $p['_']['id'] = $this->id;
        $p['_']['started'] = isset($this->started) ? date("F d, Y g:i:sa", strtotime($this->started)) : false;
        $p['_']['ended'] = isset($this->ended) ? date("F d, Y g:i:sa", strtotime($this->ended)) : false;
        $p['_']['duration'] = $this->duration;
        $p['_']['status'] = $this->status;
        if ($this->status === 'running' && $this->statusLog->paused) {
            $p['_']['status'] = 'paused';
        }
        $p['_']['estimatedTimeRemaining'] = Date::niceDuration($this->estimateTimeRemaining);
        $p['_']['log_status'] = 'fine';

        $p['_']['menu'] = [];
        $isMostRecent = $this->isMostRecent;
        if ($isMostRecent) {
            if ($this->status === 'success' && !$this->statusLog->cleaned) {
            } elseif ($this->status !== 'running') {
                $p['_']['menu'][] = ['label' => 'Run Again', 'url' => Url::to(['admin/interface/run', 'id' => $this->data_interface_id]), 'attributes' => ['data-handler' => 'background'], 'class' => 'btn-warning'];
            }
        }

        if ($this->statusLog->hasError) {
            $p['_']['log_status'] = 'error';
        } elseif ($this->statusLog->hasWarning) {
            $p['_']['log_status'] = 'warning';
        }
        $p['_']['last_update'] = $this->last_update;
        $p['_']['peak_memory'] = StringHelper::humanFilesize($this->statusLog->peakMemoryUsage);
        $p['progress'] = [
            'total' => $this->statusLog->progressTotal,
            'done' => $this->statusLog->progressDone,
        ];
        if (isset($this->statusLog->ended)) {
            $p['progress']['duration'] = Date::shortDuration($this->statusLog->ended - $this->statusLog->started);
        }
        $p['tasks'] = [];
        foreach ($this->statusLog->tasks as $id => $task) {
            $p['tasks'][$id] = $task->package;
        }
        $p['messages'] = [];
        $lasttime = $started = $this->statusLog->started;
        foreach ($this->statusLog->messages as $key => $message) {
            $key = $key . '-' . substr(md5($key), 0, 5);
            $timestamp = (float) $message['time'];
            $duration = $timestamp - $lasttime;
            $lasttime = $timestamp;
            $fromStart = $timestamp-$started;
            $p['messages'][$key] = [
                'message' => $message['message'],
                'duration' => Date::shortDuration($duration),
                'fromStart' => Date::shortDuration($fromStart),
                'level' => $message['level'],
                'data' => $message['data'],
                'memory' => StringHelper::humanFilesize($message['memory']),
            ];
        }
        $p['output'] = $this->statusLog->commandOutput;

        return $p;
    }

    /**
     * Get is active.
     *
     * @return [[@doctodo return_type:getIsActive]] [[@doctodo return_description:getIsActive]]
     */
    public function getIsActive()
    {
        return in_array($this->status, ['queued', 'running']);
    }

    /**
     * Get bootstrap state.
     *
     * @return [[@doctodo return_type:getBootstrapState]] [[@doctodo return_description:getBootstrapState]]
     */
    public function getBootstrapState()
    {
        switch ($this->status) {
          case 'success':
            return 'success';
          break;
          case 'queued':
          case 'running':
            return 'info';
          break;
          case 'error':
          case 'interrupted':
            return 'danger';
          break;
        }

        return 'warning';
    }
}
