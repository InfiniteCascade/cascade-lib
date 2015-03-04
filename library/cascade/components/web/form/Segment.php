<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form;

use cascade\components\db\fields\Model as ModelField;
use cascade\components\db\fields\Relation as RelationField;
use infinite\helpers\Html;
use infinite\web\grid\Grid;
use Yii;

/**
 * Segment [@doctodo write class description for Segment].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Segment extends FormObject
{
    /**
     */
    public $cellClass = 'cascade\components\web\form\fields\Cell';
    /**
     */
    public $subform;
    /**
     */
    public $relationSettings = true;
    /**
     */
    public $relationField;

    /**
     */
    protected $_name;
    /**
     */
    protected $_model;
    /**
     */
    protected $_settings;
    /**
     */
    protected $_grid;
    /**
     */
    protected $_fields;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->isValid =  $this->model->setFormValues($this->name);
        if (!empty($this->settings['ignoreInvalid'])) {
            $this->isValid = true;
            $this->model->clearErrors();
        }
    }

    /**
     * Set model.
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * Set name.
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Set settings.
     */
    public function setSettings($settings)
    {
        if (is_null($this->model)) {
            throw new Exception("You must set the model before you can set the settings.");
        }
        $this->_settings = $this->model->formSettings($this->name, $settings);
        if (is_null($this->_settings) and !empty($settings)) {
            $this->_settings = $settings;
        }
        if (empty($this->_settings['fields'])) {
            $this->_settings['fields'] = [];
        }
    }

    /**
     * Get model.
     *
     * @return unknown
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Get settings.
     *
     * @return unknown
     */
    public function getSettings()
    {
        if (!is_null($this->_settings)) {
            $this->settings = [];
        }

        return $this->_settings;
    }

    /**
     * Get name.
     *
     * @return unknown
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     */
    public function output()
    {
        echo $this->generate();
    }

    /**
     *
     */
    public function hasFile()
    {
        if ($this->model->getBehavior('Storage') !== null) {
            return true;
        }

        foreach ($this->fields as $field) {
            if ($field->hasFile()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return unknown
     */
    public function generate()
    {
        $this->getFields();
        $result = [];
        if (!empty($this->_settings['title'])) {
            $result[] = Html::beginTag('fieldset');
            $result[] = Html::tag('legend', $this->_settings['title']);
        }
        $result[] = $this->grid->generate();
        if (!empty($this->_settings['title'])) {
            $result[] = Html::endTag('fieldset');
        }

        return implode("\n", $result);
    }

    /**
     * Get fields.
     *
     * @return unknown
     */
    protected function getFields()
    {
        $settings = $this->settings;
        if (is_null($this->_fields)) {
            $this->_fields = [];
            if (is_array($settings)) {
                $this->_settings = $settings;
            } else {
                $this->_settings = [];
            }
            if (!isset($this->_settings['fieldSettings'])) {
                $this->_settings['fieldSettings'] = [];
            }
            if (!isset($this->_settings['formField'])) {
                $this->_settings['formField'] = [];
            }
            if (!isset($this->_settings['ignoreFields'])) {
                $this->_settings['ignoreFields'] = [];
            }
            if (is_array($this->_settings['fields']) && empty($this->_settings['fields'])) {
                $this->_settings['fields'] = null;
            }
            $fields = $this->_model->getFields($this);
            if (!isset($this->_model->_moduleHandler)) {
                $modelClass = get_class($this->_model);
                $this->_model->_moduleHandler = $modelClass::FORM_PRIMARY_MODEL;
            }
            $requiredFields = true;
            $fieldsTemplate = false;
            if (!is_null($this->relationField)) {
                $fieldName = $this->relationField->modelRole . ':' . $this->relationField->baseModel->objectType->systemId;
                if (isset($fields[$fieldName])) {
                    $fields[$fieldName]->baseModel = $this->relationField->baseModel;
                    $fields[$fieldName]->model = $this->relationField->model;
                    $fields[$fieldName]->formField = $this->relationField->formField;
                    $fields[$fieldName]->required = true;
                    $this->relationField->formField->inlineRelation = true;
                    if (!$this->relationField->model->isNewRecord && $this->relationField->companion->hasDashboard) {
                        $fieldsTemplate = [[$fieldName]];
                        $requiredFields = false;
                    }
                }
            }
            if ($requiredFields) {
                $requiredFields = $this->_model->getRequiredFields($this);
            } else {
                $requiredFields = [$fieldName => $fields[$fieldName]];
            }
            if (!$fieldsTemplate) {
                if (!empty($this->subform)) {
                    $fieldsTemplate = [[$this->subform => ['relationSettings' => $this->relationSettings]]];
                } elseif (!isset($this->_settings['fields'])) {
                    $fieldsTemplate = [];
                    foreach ($fields as $fieldName => $field) {
                        if (!$field->human) {
                            continue;
                        }
                        //if (!$field->required) { continue; }
                        if (!($field instanceof ModelField)) {
                            continue;
                        }
                        $fieldsTemplate[] = [$fieldName];
                    }
                } else {
                    $fieldsTemplate = $this->_settings['fields'];
                }
            }
            if ($fieldsTemplate !== false) {
                $this->_settings['fields'] = [];
                foreach ($fields as $fieldKey => $field) {
                    if (!is_object($field->model)) {
                        \d($field);
                        exit;
                    }
                    if ($field->model->isNewRecord) {
                        continue;
                    }
                    if ($field->human) {
                        continue;
                    }
                    if (!$field->required) {
                        continue;
                    }
                    $this->grid->prepend($field->formField);
                }
                $fields['_moduleHandler']->formField->owner = $this;
                $this->grid->prepend($fields['_moduleHandler']->formField);
                $cellClass = $this->cellClass;

                // make sure all required fields are part of the form
                if (empty($this->subform)) {
                    if (!empty($requiredFields)) {
                        foreach ($fieldsTemplate as $rowFields) {
                            foreach ($rowFields as $fieldKey => $fieldSettings) {
                                if (is_numeric($fieldKey)) {
                                    $fieldKey = $fieldSettings;
                                    $fieldSettings = [];
                                }
                                unset($requiredFields[$fieldKey]);
                            }
                        }
                    }

                    foreach ($requiredFields as $fieldName => $field) {
                        if (in_array($fieldName, $this->_settings['ignoreFields'])) {
                            continue;
                        }
                        $fieldsTemplate[] = [$fieldName];
                    }
                }

                foreach ($fieldsTemplate as $rowFields) {
                    $rowItems = [];
                    foreach ($rowFields as $fieldKey => $fieldSettings) {
                        if (is_numeric($fieldKey)) {
                            $fieldKey = $fieldSettings;
                            $fieldSettings = [];
                        }
                        if ($fieldKey === false || $fieldKey === ':empty') {
                            $rowItems[] = Yii::createObject(['class' => $cellClass, 'content' => '&nbsp;']);
                            continue;
                        }

                        if ($fieldKey === ':separator') {
                            $rowItems[] = Yii::createObject(['class' => $cellClass, 'content' => '<span class="separator"></span>']);
                            continue;
                        }

                        if (!isset($fields[$fieldKey])) {
                            \d([$fieldKey, array_keys($fields)]);
                            continue;
                        }

                        $this->_fields[$fieldKey] = $fields[$fieldKey];
                        if ($fieldKey === false) {
                            $rowItems[] = false;
                        } else {
                            //\d([$fieldKey, $fieldSettings]);
                            $cellOptions = ['class' => $cellClass, 'content' => $fields[$fieldKey]->formField->configure($fieldSettings)];
                            if (isset($cellOptions['content']->columns)) {
                                $cellOptions['columns'] = $cellOptions['content']->columns;
                            }
                            $rowItems[] = Yii::createObject($cellOptions);
                        }
                    }
                    $this->grid->addRow($rowItems);
                }
            }
        }
        //\d($this->_fields);exit;
        return $this->_fields;
    }

    /**
     * Get grid.
     */
    public function getGrid()
    {
        if (is_null($this->_grid)) {
            $this->_grid = new Grid();
        }

        return $this->_grid;
    }
}
