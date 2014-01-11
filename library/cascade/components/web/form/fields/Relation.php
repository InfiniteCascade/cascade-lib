<?php
namespace cascade\components\web\form\fields;

use infinite\helpers\ArrayHelper;
use infinite\helpers\Html;
use cascade\models\Relation as RelationModel;

class Relation extends Base {
	public $linkExisting = true;
	public $linkMultiple = false;
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
		if (is_null($model) || empty($model->primaryKey)) {
			$relationKey = $moduleHandler;

			if (is_null($model)) {
				$model = $companion->getModel();
			}
		} else {

			$relationKey = $model->primaryKey;
		}
		$relationTabularId = RelationModel::generateTabularId($relationKey);
		if (isset($this->generator->models['relations'][$relationTabularId])) {
			$relationModel = $this->generator->models['relations'][$relationTabularId];
		} else {
			$relationModel = $model->getRelationModel($relationTabularId);
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
		if ($this->linkExisting) {
			// we are matching with an existing document
			return $this->generateRelationField();
		} else {
			$formSegment = $this->relatedObject->objectType->getFormSegment($this->relatedObject, ['relationField' => $this->modelField]);
			$formSegment->owner = $this;
			return $formSegment->generate();
		}
	}

	protected function generateRelationField()
	{
		$model = $this->model;
		$companion = $this->modelField->companion;
		$module = $this->modelField->module;
		$field = $this->getModelField();
		$parts = [];
		$r = [];
		$r['module'] = $module->systemId;
		$r['companion'] = $companion->systemId;
		$r['multiple'] = $this->linkMultiple; // && $this->modelField->relationship->multiple;
		$r['role'] = $this->modelField->relationship->companionRole($this->modelField->modelRole);
		if ($this->modelField->baseModel && !$this->modelField->baseModel->isNewRecord) {
			$r['moduleObjectId'] = $this->modelField->baseModel->primaryKey;
		}
		$this->htmlOptions['data-relationship'] = json_encode($r, JSON_FORCE_OBJECT);
		Html::addCssClass($this->htmlOptions, 'relationship');
		$parts[] = Html::activeHiddenInput($model, $field, $this->htmlOptions);
		return implode($parts);
	}
}
?>