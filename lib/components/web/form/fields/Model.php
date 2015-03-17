<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\form\fields;

use canis\base\exceptions\Exception;
use canis\helpers\Html;

/**
 * Model [[@doctodo class_description:cascade\components\web\form\fields\Model]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Model extends Base
{
    /**
     * @var [[@doctodo var_type:fileStorageWidgetClass]] [[@doctodo var_description:fileStorageWidgetClass]]
     */
    public $fileStorageWidgetClass = 'cascade\components\web\widgets\form\FileStorage';
    /**
     * @var [[@doctodo var_type:_fieldConfig]] [[@doctodo var_description:_fieldConfig]]
     */
    protected $_fieldConfig = [];
    /**
     * Get field config.
     *
     * @return [[@doctodo return_type:getFieldConfig]] [[@doctodo return_description:getFieldConfig]]
     */
    public function getFieldConfig()
    {
        return array_merge([
                'template' => "<div class=\"\">{input}</div>\n<div class=\"\">{error}</div>",
                'labelOptions' => ['class' => "control-label"],
        ], $this->_fieldConfig);
    }

    /**
     * Set field config.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setFieldConfig($value)
    {
        $this->_fieldConfig = $value;
    }
    /**
     * [[@doctodo method_description:generate]].
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return unknown
     *
     */
    public function generate()
    {
        $model = $this->model;
        if (!$this->generator) {
            throw new Exception("Unable to find generator.");
        }
        if (!$this->generator->form) {
            throw new Exception("Unable to find generator form.");
        }
        $form = $this->generator->form;
        $pre = $post = null;
        $field = $this->getModelFieldName();
        $fieldConfig = $this->fieldConfig;
        $templatePrefix = '';
        if ($this->showLabel) {
            $templatePrefix = "{label}\n";
            $fieldConfig['template'] = $templatePrefix . $fieldConfig['template'];
        }
        $item = $form->field($model, $field, $fieldConfig);
        $item->inputOptions = & $this->htmlOptions;
        $item->inputOptions['value'] = $fieldConfig['value'] = $this->modelField->format->formValue;

        Html::addCssClass($this->htmlOptions, 'form-control');
        if (substr($this->type, 0, 5) === 'smart') {
            $this->type = lcfirst(substr($this->type, 5));
            if (isset($this->smartOptions['watchField'])) {
                $watchFieldId = $this->neightborFieldId($this->smartOptions['watchField']);
                if (!$watchFieldId) {
                    unset($this->smartOptions['watchField']);
                } else {
                    $this->smartOptions['watchField'] = '#' . $watchFieldId;
                }
            }
            $this->htmlOptions['data-value'] = $fieldConfig['value']; //Html::getAttributeValue($model, $field)
            $this->htmlOptions['data-smart'] = json_encode($this->smartOptions);
        }

        switch ($this->type) {
        case 'checkBox':
            $item->checkbox();
            break;
        case 'radioButton':
            $item->radio();
            break;
        case 'checkBoxList':
            $item->checkboxList($this->options);
            break;
        case 'radioButtonList':
        case 'boolean':
            if ($this->type === 'boolean') {
                $this->showLabel = false;
                if (!isset($this->options)) {
                    $this->showLabel = true;
                    $this->options = [1 => 'Yes', 0 => 'No'];
                }
                Html::addCssClass($this->htmlOptions, 'btn-group');
                Html::removeCssClass($this->htmlOptions, 'form-control');
                $this->htmlOptions['data-toggle'] = 'buttons';
                $encode = !isset($this->htmlOptions['encode']) || $this->htmlOptions['encode'];
                $this->htmlOptions['item'] = function ($index, $label, $name, $checked, $value) use ($encode) {
                    $itemOptions = ['container' => false, 'labelOptions' => ['class' => 'btn-primary btn']];
                    if ($checked) {
                        Html::addCssClass($itemOptions['labelOptions'], 'active');
                    }

                    return Html::radio($name, $checked, array_merge($itemOptions, [
                        'value' => $value,
                        'label' => $encode ? Html::encode($label) : $label,
                    ]));
                };
            }
            $item->radioList($this->options);
            break;
        case 'dropDownList':
        case 'smartDropDownList':
            $item->dropDownList($this->options);
            break;
        case 'listBox':
            $item->listBox($this->options);
            break;
        case 'file':
            // $item->fileInput();
            Html::removeCssClass($this->htmlOptions, 'form-control');
            $fileStorageWidgetClass = $this->fileStorageWidgetClass;
            $item = $fileStorageWidgetClass::widget(['item' => $item]);
            break;
        case 'hidden':
            $this->showLabel = false;
            $item = Html::activeHiddenInput($model, $field, $this->htmlOptions);
            break;
        case 'password':
            $item->password();
            break;
        case 'date':
            //$item->template = $templatePrefix . "<div class=\"input-group date\">{input}<span class=\"input-group-addon\"></span></div>\n<div class=\"\">{error}</div>";
            if (!$item->inputGroupPostfix) {
                $item->inputGroupPostfix = "<i class=\"fa fa-calendar\"></i>";
            }
            Html::addCssClass($item->inputGroupHtmlOptions, 'date');
            break;
        case 'textarea':
            $item->textarea();
            break;
        case 'rich':
            Html::addCssClass($this->htmlOptions, 'rich');
            $editorSettings = [
                ];
            $this->htmlOptions['data-editor'] = Json::encode($editorSettings);
            $item = Html::activeTextArea($model, $field, $this->htmlOptions);
            break;
        }
        if (!empty($item)) {
            return $pre . $item . $post;
        }

        return false;
    }
}
