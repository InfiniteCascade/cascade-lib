<?php
namespace cascade\components\web\form\fields;

use infinite\db\ActiveRecord;

class Relation extends Base {
	public $linkExisting = true;
	/**
	 *
	 *
	 * @param unknown $model        (optional)
	 * @param unknown $formSettings (optional)
	 * @return unknown
	 */
	public function generate() {
		$companion = $this->modelField->companion;
		//\var_dump($companion);exit;
		if ($this->linkExisting) {
			// we are matching with an existing document
			return 'existing';
		} else {
			$moduleHandler = $this->modelField->moduleHandler;
			$moduleHandlerKey = ActiveRecord::generateTabularId($moduleHandler);
			$model = null;
			if (isset($this->generator->models[$moduleHandlerKey])) {
				$model = $this->generator->models[$moduleHandlerKey];
			} else {
				$model = $companion->getModel();
				$model->_moduleHandler = $moduleHandler;
			}

			$formSegment = $companion->getFormSegment($model, ['relationField' => $this->modelField]);
			$formSegment->owner = $this;
			return $formSegment->generate();
		}
	}
}
?>