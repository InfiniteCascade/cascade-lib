<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\form;

use canis\helpers\Html;
use Yii;

/**
 * Generator [[@doctodo class_description:cascade\components\web\form\Generator]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Generator extends \canis\base\Object implements \canis\web\RenderInterface
{
    /**
     * @var [[@doctodo var_type:_items]] [[@doctodo var_description:_items]]
     */
    protected $_items;
    /**
     * @var [[@doctodo var_type:form]] [[@doctodo var_description:form]]
     */
    public $form;

    /**
     * @var [[@doctodo var_type:models]] [[@doctodo var_description:models]]
     */
    public $models = [];

    /**
     * @var [[@doctodo var_type:isValid]] [[@doctodo var_description:isValid]]
     */
    public $isValid = true;
    /**
     * @var [[@doctodo var_type:class]] [[@doctodo var_description:class]]
     */
    public $class = '';
    /**
     * @var [[@doctodo var_type:ajax]] [[@doctodo var_description:ajax]]
     */
    public $ajax = false;

    /**
     * Set items.
     *
     * @param [[@doctodo param_type:items]] $items [[@doctodo param_description:items]]
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
     * [[@doctodo method_description:generate]].
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
     * [[@doctodo method_description:hasFile]].
     *
     * @return [[@doctodo return_type:hasFile]] [[@doctodo return_description:hasFile]]
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
     * [[@doctodo method_description:output]].
     */
    public function output()
    {
        echo $this->generate();
    }
}
