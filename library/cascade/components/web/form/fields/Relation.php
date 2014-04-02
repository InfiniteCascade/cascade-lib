<?php
namespace cascade\components\web\form\fields;

use infinite\helpers\ArrayHelper;
use infinite\helpers\Html;
use cascade\models\Relation as RelationModel;

use cascade\components\web\browser\Response as BrowserResponse;

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

	public function getRelationModelField()
	{
		$field = $this->model->tabularPrefix;
		if ($this->modelField->relationship->companionRole($this->modelField->modelRole) === 'child') {
			$field .= 'child_object_id';
		} else {
			$field .= 'parent_object_id';
		}
		return $field;
	}

	protected function generateRelationField()
	{
		$model = $this->model;
		$field = $this->getRelationModelField();
		$parts = [];
		$r = ['context' => [], 'browse' => [], 'search' => ['data' => []]];
		$r['context']['relationship'] = $this->modelField->relationship->systemId;
		if ($this->modelField->baseModel && !$this->modelField->baseModel->isNewRecord) {
			$r['context']['objectId'] = $this->modelField->baseModel->primaryKey;
		}
		$r['context']['role'] = $role = $this->modelField->relationship->companionRole($this->modelField->modelRole);
		// \d($role);exit;
		
		if (($modelTypeItem = $this->modelField->relationship->{$role}->collectorItem)) {
			$typeBundle = BrowserResponse::handleInstructions(['handler' => 'types', 'relationshipRole' => $role, 'relationship' => $this->modelField->relationship->systemId]);
			$r['browse']['root'] = $typeBundle->package();
		}
		$r['multiple'] = $this->linkMultiple; // && $this->modelField->relationship->multiple;
		$this->htmlOptions['data-relationship'] = json_encode($r, JSON_FORCE_OBJECT);
		Html::addCssClass($this->htmlOptions, 'relationship');
		$parts[] = Html::activeHiddenInput($model, $field, $this->htmlOptions);
		return implode($parts);
	}

    /**
     * Gets the value of linkExisting.
     *
     * @return mixed
     */
    public function getLinkExisting()
    {
        return $this->linkExisting;
    }
    
    /**
     * Sets the value of linkExisting.
     *
     * @param mixed $linkExisting the link existing
     *
     * @return self
     */
    public function setLinkExisting($linkExisting)
    {
        $this->linkExisting = $linkExisting;

        return $this;
    }

    /**
     * Gets the value of linkMultiple.
     *
     * @return mixed
     */
    public function getLinkMultiple()
    {
        return $this->linkMultiple;
    }
    
    /**
     * Sets the value of linkMultiple.
     *
     * @param mixed $linkMultiple the link multiple
     *
     * @return self
     */
    public function setLinkMultiple($linkMultiple)
    {
        $this->linkMultiple = $linkMultiple;

        return $this;
    }

    /**
     * Gets the value of relatedObject.
     *
     * @return mixed
     */
    public function getRelatedObject()
    {
        return $this->relatedObject;
    }
    
    /**
     * Sets the value of relatedObject.
     *
     * @param mixed $relatedObject the related object
     *
     * @return self
     */
    public function setRelatedObject($relatedObject)
    {
        $this->relatedObject = $relatedObject;

        return $this;
    }
}
?>