<?php
namespace cascade\components\rest;
trait ActionTrait
{
	protected $_models = [];

    public function findModel($id)
    {
        if (!isset($this->_models[$id])) {
        	$this->_models[$id] = parent::findModel($id);
        }
        return $this->_models[$id];
    }

	public function init()
	{
		$this->attachBehaviors($this->behaviors());
	}

	public function behaviors()
	{
		return [
			'Param' => ['class' => 'cascade\components\rest\ParamBehavior']
		];
	}

	protected function beforeRun()
    {
    	$action = new ActionEvent;
    	$action->sender = $this;
    	$this->trigger(Action::EVENT_BEFORE_RUN);
    	if (!$action->isValid) {
    		return false;
    	}
    	if (!parent::beforeRun()) {
    		return false;
    	}
		return true;	
	}
	protected function afterRun()
    {
    	parent::afterRun();
    	$this->trigger(Action::EVENT_AFTER_RUN);
    }
}
?>