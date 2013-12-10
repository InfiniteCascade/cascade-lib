<?php
namespace cascade\components\web\form\fields;

use cascade\models\Relation as RelationModel;

class Relation extends Base {
	public $linkExisting = true;
	public $relatedObject;

	public function init() {
		parent::init();
		$moduleHandler = implode(':', array_slice(explode(':', $this->modelField->moduleHandler), 0, 2));
		$model = $relationModel = null;
		$companion = $this->modelField->companion;
		foreach ($this->generator->models as $key => $modelTest) {
			if ($key === 'relations') {
				continue;
			}
			if ($modelTest->_moduleHandler === $moduleHandler) {
				$model = $modelTest;
			}
		}

		if (is_null($model)) {
			$model = $companion->getModel();
			$relationKey = $moduleHandler;
		} else {
			$relationKey = $model->primaryKey;
		}
		$relationKeyKey = RelationModel::generateTabularId($relationKey);
		if (isset($this->generator->models['relations'][$relationKeyKey])) {
			$relationModel = $this->generator->models['relations'][$relationKeyKey];
		} else {
			$relationModel = $model->getRelationModel($relationKeyKey);
		}
		$model->_moduleHandler = $moduleHandler;
		$this->modelField->model = $relationModel;
		$this->relatedObject = $model;
	}
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

			$formSegment = $companion->getFormSegment($this->relatedObject, ['relationField' => $this->modelField]);
			$formSegment->owner = $this;
			return $formSegment->generate();
		}
	}
}
?>