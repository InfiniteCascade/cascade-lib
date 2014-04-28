<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form\fields;

use Yii;
use infinite\helpers\Html;
use cascade\models\Relation as RelationModel;

use cascade\components\web\browser\Response as BrowserResponse;

/**
 * Relation [@doctodo write class description for Relation]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Relation extends Base
{
    /**
     * @var __var_linkExisting_type__ __var_linkExisting_description__
     */
    public $linkExisting = true;
    /**
     * @var __var_linkExisting_type__ __var_linkExisting_description__
     */
    public $lockFields = [];
    /**
     * @var __var_inlineRelation_type__ __var_inlineRelation_description__
     */
    public $inlineRelation = false;
    /**
     * @var __var_linkMultiple_type__ __var_linkMultiple_description__
     */
    public $linkMultiple = false;

    public function getRelatedObject()
    {
        return $this->modelField->value;
    }

    /**
     * __method_generate_description__
     * @return unknown
     */
    public function generate()
    {
        //$this->relatedObject->setParentModel($this->modelField->baseModel);
        if ($this->linkExisting) {
            $this->model->setParentModel($this->modelField->baseModel);
            // we are matching with an existing document
            if ($this->linkExisting === true) {
                $template = 'simple';
            } else {
                $template = 'hierarchy';
            }
            return $this->generateRelationField(['template' => $template]);
        } elseif ($this->inlineRelation) {
            //$this->model->setParentModel($this->modelField->baseModel, false);
            $this->model->setParentModel($this->relatedObject);
            // we are matching with an existing document
            return $this->generateRelationField(['template' => 'fields']);
        } elseif (!empty($this->relatedObject)) {
            $this->model->setParentModel($this->relatedObject);
            $formSegment = $this->relatedObject->objectType->getFormSegment($this->relatedObject, ['relationField' => $this->modelField]);
            $formSegment->owner = $this;
            return $formSegment->generate();
        } else {
            return null;
        }
    }

    /**
     * Get relation model field
     * @return __return_getRelationModelField_type__ __return_getRelationModelField_description__
     */
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

    /**
     * __method_generateRelationField_description__
     * @param array                                 $initialSettings __param_initialSettings_description__ [optional]
     * @return __return_generateRelationField_type__ __return_generateRelationField_description__
     */
    protected function generateRelationField($initialSettings = [])
    {
        $model = $this->model;
        $field = $this->getRelationModelField();
        $parts = [];
        $r = $initialSettings;
        $r['title'] = $this->modelField->label;
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
        $companionType = $this->modelField->relationship->companionRoleType($this->modelField->modelRole);
        $r['selector']['inputLabel'] = 'Select '. $companionType->title->upperSingular;
        //\d($r);exit;

        if (($modelTypeItem = $this->modelField->relationship->{$role}->collectorItem)) {
            $typeBundle = BrowserResponse::handleInstructions(['handler' => 'types', 'relationshipRole' => $role, 'relationship' => $this->modelField->relationship->systemId, 'typeFilters' => ['hasDashboard']]);
            $r['selector']['browse']['root'] = $typeBundle->package();
        }
        $r['model'] = [
            'prefix' => $this->model->formName() . $this->model->tabularPrefix,
            'attributes' => array_merge($this->model->attributes, ['taxonomy_id' => $this->model->taxonomy_id])
        ];
        if (!empty($this->modelField->value->primaryKey)) {
            $r['select'] = [
                'id' => $this->modelField->value->primaryKey,
                'descriptor' => $this->modelField->value->descriptor,
                'subdescriptor' => $this->modelField->value->primarySubdescriptor
            ];
        }
        if (!empty($r['model']['attributes']['start'])) {
            $r['model']['attributes']['start'] = Yii::$app->formatter->asDate($r['model']['attributes']['start']);
        }
        if (!empty($r['model']['attributes']['end'])) {
            $r['model']['attributes']['end'] = Yii::$app->formatter->asDate($r['model']['attributes']['end']);
        }
        $r['lockFields'] = $this->lockFields;
        $r['multiple'] = $this->linkMultiple; // && $this->modelField->relationship->multiple;
        $this->htmlOptions['data-relationship'] = json_encode($r, JSON_FORCE_OBJECT);
        Html::addCssClass($this->htmlOptions, 'relationship');
        //$model->_moduleHandler = $this->modelField->relationship->companionRole($this->modelField->modelRole) .':'. $this->modelField->relationship->companionRoleType($this->modelField->modelRole)->systemId;
        $parts[] = Html::activeHiddenInput($model, $this->model->tabularPrefix . '_moduleHandler');
        $parts[] = Html::activeHiddenInput($model, $field, $this->htmlOptions);

        return implode($parts);
    }

    /**
     * Gets the value of linkExisting.
     * @return mixed
     */
    public function getLinkExisting()
    {
        return $this->linkExisting;
    }

    /**
     * Sets the value of linkExisting.
     * @param mixed $linkExisting the link existing
     * @return self
     */
    public function setLinkExisting($linkExisting)
    {
        $this->linkExisting = $linkExisting;

        return $this;
    }

    /**
     * Gets the value of linkMultiple.
     * @return mixed
     */
    public function getLinkMultiple()
    {
        return $this->linkMultiple;
    }

    /**
     * Sets the value of linkMultiple.
     * @param mixed $linkMultiple the link multiple
     * @return self
     */
    public function setLinkMultiple($linkMultiple)
    {
        $this->linkMultiple = $linkMultiple;

        return $this;
    }

}
