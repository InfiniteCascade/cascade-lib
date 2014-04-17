<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form;

use Yii;

use cascade\components\db\fields\Model as ModelField;

use infinite\web\grid\Grid;
use infinite\helpers\Html;

/**
 * Segment [@doctodo write class description for Segment]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Segment extends FormObject
{
    /**
     * @var __var_cellClass_type__ __var_cellClass_description__
     */
    public $cellClass = 'cascade\components\web\form\fields\Cell';
    /**
     * @var __var_subform_type__ __var_subform_description__
     */
    public $subform;
    /**
     * @var __var_linkExisting_type__ __var_linkExisting_description__
     */
    public $linkExisting = true;
    /**
     * @var __var_relationField_type__ __var_relationField_description__
     */
    public $relationField;

    /**
     * @var __var__name_type__ __var__name_description__
     */
    protected $_name;
    /**
     * @var __var__model_type__ __var__model_description__
     */
    protected $_model;
    /**
     * @var __var__settings_type__ __var__settings_description__
     */
    protected $_settings;
    /**
     * @var __var__grid_type__ __var__grid_description__
     */
    protected $_grid;
    /**
     * @var __var__fields_type__ __var__fields_description__
     */
    protected $_fields;

    /**
    * @inheritdoc
    **/
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
     * __method_setModel_description__
     * @param __param_model_type__ $model __param_model_description__
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * __method_setName_description__
     * @param __param_name_type__ $name __param_name_description__
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * __method_setSettings_description__
     * @param __param_settings_type__ $settings __param_settings_description__
     * @throws Exception __exception_Exception_description__
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
     * __method_getModel_description__
     * @return unknown
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * __method_getSettings_description__
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
     * __method_getName_description__
     * @return unknown
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * __method_output_description__
     */
    public function output()
    {
        echo $this->generate();
    }

    /**
     * __method_hasFile_description__
     * @return __return_hasFile_type__ __return_hasFile_description__
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
     * __method_generate_description__
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
     * __method_getFields_description__
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

            $requiredFields = $this->_model->getRequiredFields($this);
            $fieldsTemplate = false;
            if (!is_null($this->relationField)) {
                // $this->relationField->model->addFields($this->_model, $fields, $this->relationField->relationship, $this);
                // \d(get_class($this->relationField));
                $fields['relation'] = $this->relationField;
                $this->relationField->formField->inlineRelation = true;
                if (!$this->relationField->model->isNewRecord && $this->relationField->companion->hasDashboard) {
                    $fieldsTemplate = [['relation']];
                    $requiredFields = [];
                }
                //
                $requiredFields['relation'] = $fields['relation'];
            }
            if (!$fieldsTemplate) {
                if (!empty($this->subform)) {
                    $fieldsTemplate = [[$this->subform => ['linkExisting' => $this->linkExisting]]];
                } elseif (!isset($this->_settings['fields'])) {
                    $fieldsTemplate = [];
                    foreach ($fields as $fieldName => $field) {
                        if (!$field->human) { continue; }
                        //if (!$field->required) { continue; }
                        if (!($field instanceof ModelField)) { continue; }
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
                        \d($field);exit;
                    }
                    if ($field->model->isNewRecord) { continue; }
                    if ($field->human) { continue; }
                    if (!$field->required) { continue; }
                    $this->grid->prepend($field->formField);
                }
                $this->grid->prepend($fields['_moduleHandler']->formField);
                $cellClass = $this->cellClass;

                if (empty($this->subform)) {
                    // make sure all required fields are part of the form
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
                        if (in_array($fieldName, $this->_settings['ignoreFields'])) { continue; }
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

                        if (!isset($fields[$fieldKey])) { continue; }

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

        return $this->_fields;
    }

    /**
     * __method_getGrid_description__
     * @return __return_getGrid_type__ __return_getGrid_description__
     */
    public function getGrid()
    {
        if (is_null($this->_grid)) {
            $this->_grid = new Grid;
        }

        return $this->_grid;
    }

}
