<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use cascade\models\DataInterface;
use cascade\models\DataInterfaceLog;

/**
 * Item [@doctodo write class description for Item]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeferredAction extends \infinite\deferred\components\Action
{
	use ActionTrait;

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


    public function pauseAction()
    {
		$logModel = $this->getLogModel(true);
    	return $logModel->statusLog->pause();
    }

    public function resumeAction()
    {
		$logModel = $this->getLogModel(true);
    	return $logModel->statusLog->resume();
    }

	public function getDescriptor()
	{
		$logModel = $this->getLogModel(true);
		if (empty($logModel) || !isset($logModel->dataInterface)) {
			return 'Unknown Data Interface';
		}
		return 'Interface: ' . $logModel->dataInterface->name;
	}
	
	public function cancel()
	{
		return $this->cancelLog();
	}
	
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
		return null;
	}

	public function requiredConfigParams()
	{
		return ['logModel'];
	}

    public function getResultConfig()
	{
		return [
			'class' => DeferredResult::className()
		];
	}
}
