<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form;

use Yii;
use infinite\helpers\Html;

/**
 * Generator [@doctodo write class description for Generator].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Generator extends \infinite\base\Object implements \infinite\web\RenderInterface
{
    /**
     * @var __var__items_type__ __var__items_description__
     */
    protected $_items;
    /**
     * @var __var_form_type__ __var_form_description__
     */
    public $form;

    /**
     * @var __var_models_type__ __var_models_description__
     */
    public $models = [];

    /**
     * @var __var_isValid_type__ __var_isValid_description__
     */
    public $isValid = true;
    /**
     * @var __var_class_type__ __var_class_description__
     */
    public $class = '';
    /**
     * @var __var_ajax_type__ __var_ajax_description__
     */
    public $ajax = false;

    /**
     * Set items.
     *
     * @param __param_items_type__ $items __param_items_description__
     */
    public function setItems($items)
    {
        $this->_items = $items;
        if (isset($this->_items[0]) && is_array($this->_items[0])) {
            $this->_items = $this->_items[0];
        }
        foreach ($this->_items as $item) {
            $item->owner = $this;
            if (!$item->isValid) {
                $this->isValid = false;
            }
        }
    }

    /**
     * __method_generate_description__.
     *
     * @return unknown
     */
    public function generate()
    {
        if (empty($this->_items)) {
            return '';
        }
        $result = [];
        $formOptions = [
            'options' => ['class' => ''], //form-horizontal
            'enableClientValidation' => false,
        ];
        if (Yii::$app->request->isAjax) {
            Html::addCssClass($formOptions['options'], 'ajax');
        }

        if ($this->hasFile() && !isset($formOptions['options']['enctype'])) {
            $formOptions['options']['enctype'] = 'multipart/form-data';
        }

        list($this->form, $formStartRow) = ActiveForm::begin($formOptions, false);
        $result[] = $formStartRow;
        // $result[] = Html::beginForm('', 'post', array('class' => $this->class));
        $result[] = Html::beginTag('div', ['class' => '']);
        foreach ($this->_items as $item) {
            $result[] = $item->generate();
        }
        //if (!Yii::$app->request->isAjax) {
            $result[] = Html::beginTag('div', ['class' => 'row form-group submit-group']);
        $result[] = Html::beginTag('div', ['class' => 'col-sm-12']);
        $result[] = Html::submitButton('Save', ['class' => 'btn btn-primary']);
        $result[] = Html::endTag('div');
        $result[] = Html::endTag('div');
        //}
        $result[] = Html::endTag('div');
        $result[] = ActiveForm::end(false);

        return implode("\n", $result);
    }

    /**
     * __method_hasFile_description__.
     *
     * @return __return_hasFile_type__ __return_hasFile_description__
     */
    public function hasFile()
    {
        foreach ($this->_items as $item) {
            if ($item->hasFile()) {
                return true;
            }
        }

        return false;
    }

    /**
     * __method_output_description__.
     */
    public function output()
    {
        echo $this->generate();
    }
}
