<?php
namespace cascade\components\web\form\fields;

use Yii;
use infinite\helpers\Html;
use cascade\models\Relation as RelationModel;

use cascade\components\web\browser\Response as BrowserResponse;

class Relation extends Base
{
    public $linkExisting = true;
    public $inlineRelation = false;
    public $linkMultiple = false;
    public $relatedObject;

    public function init()
    {
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
     * @param  unknown $model        (optional)
     * @param  unknown $formSettings (optional)
     * @return unknown
     */
    public function generate()
    {
        if ($this->linkExisting) {
            // we are matching with an existing document
            return $this->generateRelationField();
        } elseif ($this->inlineRelation) {
            // we are matching with an existing document
            return $this->generateRelationField(['justFields' => true]);
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

    protected function generateRelationField($initialSettings = [])
    {
        $model = $this->model;
        $field = $this->getRelationModelField();
        $parts = [];
        $r = $initialSettings;
        $r['context'] = [];
        $r['selector'] = ['browse' => [], 'search' => ['data' => []]];
        if ($this->modelField->relationship->temporal && empty($this->model->start)) {
            $this->model->start = date("m/d/Y");
        }
        $r['context']['relationship'] = $this->modelField->relationship->package();
        if ($this->modelField->baseModel && !$this->modelField->baseModel->isNewRecord) {
            $r['context']['object'] = ['id' => $this->modelField->baseModel->primaryKey, 'descriptor' => $this->modelField->baseModel->descriptor];
        }
        $r['context']['role'] = $role = $this->modelField->relationship->companionRole($this->modelField->modelRole);
        //\d($r);exit;

        if (($modelTypeItem = $this->modelField->relationship->{$role}->collectorItem)) {
            $typeBundle = BrowserResponse::handleInstructions(['handler' => 'types', 'relationshipRole' => $role, 'relationship' => $this->modelField->relationship->systemId, 'typeFilters' => ['hasDashboard']]);
            $r['selector']['browse']['root'] = $typeBundle->package();
        }
        $r['model'] = [
            'prefix' => $this->model->formName() . $this->model->tabularPrefix,
            'attributes' => array_merge($this->model->attributes, ['taxonomy_id' => $this->model->taxonomy_id])
        ];
        if (!empty($r['model']['attributes']['start'])) {
            $r['model']['attributes']['start'] = Yii::$app->formatter->asDate($r['model']['attributes']['start']);
        }
        if (!empty($r['model']['attributes']['end'])) {
            $r['model']['attributes']['end'] = Yii::$app->formatter->asDate($r['model']['attributes']['end']);
        }
        $r['multiple'] = $this->linkMultiple; // && $this->modelField->relationship->multiple;
        $this->htmlOptions['data-relationship'] = json_encode($r, JSON_FORCE_OBJECT);
        Html::addCssClass($this->htmlOptions, 'relationship');
        $model->_moduleHandler = $this->modelField->relationship->companionRole($this->modelField->modelRole) .':'. $this->modelField->relationship->companionRoleType($this->modelField->modelRole)->systemId;
        $parts[] = Html::activeHiddenInput($model, $this->model->tabularPrefix . '_moduleHandler');
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
