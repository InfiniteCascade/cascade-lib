<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form\fields;

use cascade\components\web\browser\Response as BrowserResponse;
use infinite\helpers\Html;
use Yii;

/**
 * Relation [[@doctodo class_description:cascade\components\web\form\fields\Relation]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Relation extends Base
{
    /**
     * @var [[@doctodo var_type:relationSettings]] [[@doctodo var_description:relationSettings]]
     */
    public $relationSettings = true;
    /**
     * @var [[@doctodo var_type:lockFields]] [[@doctodo var_description:lockFields]]
     */
    public $lockFields = [];
    /**
     * @var [[@doctodo var_type:inlineRelation]] [[@doctodo var_description:inlineRelation]]
     */
    public $inlineRelation = false;
    /**
     * @var [[@doctodo var_type:linkMultiple]] [[@doctodo var_description:linkMultiple]]
     */
    public $linkMultiple = false;

    /**
     * Get related object.
     *
     * @return [[@doctodo return_type:getRelatedObject]] [[@doctodo return_description:getRelatedObject]]
     */
    public function getRelatedObject()
    {
        return $this->modelField->value;
    }

    /**
     * [[@doctodo method_description:generate]].
     *
     * @return unknown
     */
    public function generate()
    {
        //$this->relatedObject->setParentModel($this->modelField->baseModel);
        if ($this->relationSettings) {
            $this->model->setParentModel($this->modelField->baseModel);
            // we are matching with an existing document
            $relationSettings = $this->relationSettings;
            if ($relationSettings === true) {
                $relationSettings = ['template' => 'simple'];
            }
            if (!is_array($relationSettings)) {
                $relationSettings = [];
            }
            if (!isset($relationSettings['template'])) {
                $relationSettings['template'] = 'hierarchy';
            }

            return $this->generateRelationField($relationSettings);
        } elseif ($this->inlineRelation) {
            //$this->model->setParentModel($this->modelField->baseModel, false);
            $this->model->setParentModel($this->relatedObject);
            // we are matching with an existing document
            return $this->generateRelationField(['template' => 'fields']);
        } elseif (!empty($this->relatedObject)) {
            $this->model->setParentModel($this->relatedObject);
            $formSegment = $this->relatedObject->objectType->getFormSegment($this->relatedObject, ['relationField' => $this->modelField]);
            $formSegment->owner = $this->owner;

            return $formSegment->generate();
        } else {
            return;
        }
    }

    /**
     * Get relation model field.
     *
     * @return [[@doctodo return_type:getRelationModelField]] [[@doctodo return_description:getRelationModelField]]
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
     * [[@doctodo method_description:generateRelationField]].
     *
     * @param array $initialSettings [[@doctodo param_description:initialSettings]] [optional]
     *
     * @return [[@doctodo return_type:generateRelationField]] [[@doctodo return_description:generateRelationField]]
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
        $r['selector']['inputLabel'] = 'Select ' . $companionType->title->upperSingular;
        //\d($r);exit;

        if (($modelTypeItem = $this->modelField->relationship->{$role}->collectorItem)) {
            $typeBundle = BrowserResponse::handleInstructions(['handler' => 'types', 'relationshipRole' => $role, 'relationship' => $this->modelField->relationship->systemId, 'typeFilters' => ['hasDashboard']]);
            $r['selector']['browse']['root'] = $typeBundle->package();
        }
        $r['model'] = [
            'prefix' => $this->model->formName() . $this->model->tabularPrefix,
            'attributes' => array_merge($this->model->attributes, ['taxonomy_id' => $this->model->taxonomy_id]),
        ];
        if (!empty($this->modelField->value->primaryKey)) {
            $r['select'] = [
                'id' => $this->modelField->value->primaryKey,
                'descriptor' => $this->modelField->value->descriptor,
                'subdescriptor' => $this->modelField->value->primarySubdescriptor,
            ];
        }
        if (!empty($r['model']['attributes']['start'])) {
            $r['model']['attributes']['start'] = Yii::$app->formatter->asDate($r['model']['attributes']['start']);
        }
        if (!empty($r['model']['attributes']['end'])) {
            $r['model']['attributes']['end'] = Yii::$app->formatter->asDate($r['model']['attributes']['end']);
        }
        $r['lockFields'] = isset($this->relationSettings['lockFields']) ? array_merge($this->relationSettings['lockFields'], $this->lockFields) : $this->lockFields;
        $r['multiple'] = $this->linkMultiple; // && $this->modelField->relationship->multiple;
        $this->htmlOptions['data-relationship'] = json_encode($r, JSON_FORCE_OBJECT);
        Html::addCssClass($this->htmlOptions, 'relationship');
        //$model->_moduleHandler = $this->modelField->relationship->companionRole($this->modelField->modelRole) .':'. $this->modelField->relationship->companionRoleType($this->modelField->modelRole)->systemId;
        $parts[] = Html::activeHiddenInput($model, $this->model->tabularPrefix . '_moduleHandler');
        $parts[] = Html::activeHiddenInput($model, $field, $this->htmlOptions);

        return implode($parts);
    }

    /**
     * Gets the value of relationSettings.
     *
     * @return mixed
     */
    public function getLinkExisting()
    {
        return $this->relationSettings;
    }

    /**
     * Sets the value of relationSettings.
     *
     * @param mixed $relationSettings the link existing
     *
     * @return self
     */
    public function setLinkExisting($relationSettings)
    {
        $this->relationSettings = $relationSettings;

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
}
