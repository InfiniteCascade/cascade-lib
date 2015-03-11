<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\dataInterface;

use cascade\models\DataInterface;
use cascade\models\DataInterfaceLog;

/**
 * DeferredAction [[@doctodo class_description:cascade\components\dataInterface\DeferredAction]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeferredAction extends \teal\deferred\components\Action
{
    use ActionTrait;

    /**
     * [[@doctodo method_description:run]].
     *
     * @return [[@doctodo return_type:run]] [[@doctodo return_description:run]]
     */
    public function run()
    {
        $logModel = $this->getLogModel(true);
        if (!$logModel) {
            $this->result->isSuccess = false;
            $this->result->message = 'Log model no longer exists';

            return false;
        }

        if (!$logModel->run($this)) {
            $this->result->isSuccess = false;
            $this->result->message = 'Interface failed to run';
            $this->cancelLog();

            return false;
        }

        $this->result->isSuccess = true;
        $this->result->message = 'Interface ran successfully';

        return true;
    }

    /**
     * @inheritdoc
     */
    public function pauseAction()
    {
        $logModel = $this->getLogModel(true);

        return $logModel->statusLog->pause();
    }

    /**
     * @inheritdoc
     */
    public function resumeAction()
    {
        $logModel = $this->getLogModel(true);

        return $logModel->statusLog->resume();
    }

    /**
     * Get descriptor.
     *
     * @return [[@doctodo return_type:getDescriptor]] [[@doctodo return_description:getDescriptor]]
     */
    public function getDescriptor()
    {
        $logModel = $this->getLogModel(true);
        if (empty($logModel) || !isset($logModel->dataInterface)) {
            return 'Unknown Data Interface';
        }

        return 'Interface: ' . $logModel->dataInterface->name;
    }

    /**
     * @inheritdoc
     */
    public function cancel()
    {
        return $this->cancelLog();
    }

    /**
     * [[@doctodo method_description:cancelLog]].
     *
     * @return [[@doctodo return_type:cancelLog]] [[@doctodo return_description:cancelLog]]
     */
    public function cancelLog()
    {
        $logModel = $this->getLogModel(true);
        if (empty($logModel)) {
            return true;
        }
        if ($logModel->status === 'queued') {
            return $logModel->delete();
        } else {
            return false;
        }
    }

    /**
     * Get log model.
     *
     * @param boolean $refresh [[@doctodo param_description:refresh]] [optional]
     *
     * @return [[@doctodo return_type:getLogModel]] [[@doctodo return_description:getLogModel]]
     */
    public function getLogModel($refresh = false)
    {
        $config = $this->config;
        if (isset($config['logModel'])) {
            if (!is_object($config['logModel'])) {
                if ($refresh) {
                    return DataInterfaceLog::find()->where(['id' => $config['logModel']])->one();
                } else {
                    return DataInterfaceLog::get($config['logModel']);
                }
            }
            if ($refresh) {
                return DataInterfaceLog::find()->where(['id' => $config['logModel']->primaryKey])->one();
            }

            return $config['logModel'];
        }

        return;
    }

    /**
     * @inheritdoc
     */
    public function requiredConfigParams()
    {
        return ['logModel'];
    }

    /**
     * @inheritdoc
     */
    public function getResultConfig()
    {
        return [
            'class' => DeferredResult::className(),
        ];
    }
}
