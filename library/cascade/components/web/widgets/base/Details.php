<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;
use cascade\components\web\widgets\Widget;

/**
 * Details [@doctodo write class description for Details]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Details extends Widget implements ListWidgetInterface
{
    use ListWidgetTrait, ObjectWidgetTrait {
        ObjectWidgetTrait::getListItemOptions insteadof ListWidgetTrait;
        ListWidgetTrait::getListItemOptions as getListItemOptionsBase;
        ObjectWidgetTrait::buildContext insteadof ListWidgetTrait;
        ListWidgetTrait::buildContext as buildContextBase;
    }

    /**
     * @var __var_contentHtmlOptions_type__ __var_contentHtmlOptions_description__
     */
    public $contentHtmlOptions = ['class' => 'form-panel'];
    /**
     * @var __var_fieldHtmlOptions_type__ __var_fieldHtmlOptions_description__
     */
    public $fieldHtmlOptions = ['class' => 'form-group'];
    /**
     * @var __var_labelHtmlOptions_type__ __var_labelHtmlOptions_description__
     */
    public $labelHtmlOptions = ['class' => 'control-label', ];
    /**
     * @var __var_valueHtmlOptions_type__ __var_valueHtmlOptions_description__
     */
    public $valueHtmlOptions = ['class' => 'form-control-static'];

    /**
     * @inheritdoc
     */
    public $gridClass = 'infinite\\web\\grid\\Grid';
    /**
     * @inheritdoc
     */
    public $gridCellClass = 'infinite\\web\\grid\\Cell';
    /**
     * @inheritdoc
     */
    protected $_title = 'Details';

    /**
    * @inheritdoc
     */
    public function getHeaderMenu()
    {
        $menu = [];

        return $menu;
    }

    /**
    * @inheritdoc
     */
    public function generateContent()
    {
        if (empty(Yii::$app->request->object)) { return false; }
        if (!($detailFields = Yii::$app->request->object->getDetailFields()) || empty($detailFields)) { return false; }
        $parts = [];
        $parts[] = Html::beginTag('div', $this->contentHtmlOptions);

        $grid = Yii::createObject(['class' => $this->gridClass]);
        // $parts[] = '<pre>'. print_r(array_keys($detailFields), true) .'</pre>';
        foreach ($detailFields as $key => $field) {
            $fieldHtmlOptions = $this->fieldHtmlOptions;
            $labelCell = $this->generateCell(Html::tag('label', $field->label, $this->labelHtmlOptions));
            $valueCell = $this->generateCell(Html::tag('p', $field->formattedValue, $this->valueHtmlOptions));
            $row = $grid->addRow([$labelCell, $valueCell]);

            if ($field->multiline) {
                $labelCell->columns = 12;
                $valueCell->columns = 12;
                Html::addCssClass($row->htmlOptions, 'form-vertical');
            } else {
                $labelCell->columns = 5;
                $valueCell->columns = 7;
                Html::addCssClass($row->htmlOptions, 'form-horizontal');
            }
        }

        $parts[] = $grid->generate();
        $parts[] = Html::endTag('div');

        return implode($parts);
    }

    /**
     * __method_generateCell_description__
     * @param __param_content_type__       $content __param_content_description__
     * @return __return_generateCell_type__ __return_generateCell_description__
     */
    protected function generateCell($content)
    {
        return Yii::createObject(['class' => $this->gridCellClass, 'content' => $content, 'tabletSize' => false]);
    }

    /**
     * Get pagination settings
     * @return __return_getPaginationSettings_type__ __return_getPaginationSettings_description__
     */
    public function getPaginationSettings()
    {
        return false;
    }
}
