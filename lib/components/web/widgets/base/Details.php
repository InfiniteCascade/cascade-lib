<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\widgets\base;

use cascade\components\web\widgets\Widget;
use canis\helpers\Html;
use Yii;

/**
 * Details [[@doctodo class_description:cascade\components\web\widgets\base\Details]].
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
     * @var [[@doctodo var_type:contentHtmlOptions]] [[@doctodo var_description:contentHtmlOptions]]
     */
    public $contentHtmlOptions = ['class' => 'form-panel'];
    /**
     * @var [[@doctodo var_type:fieldHtmlOptions]] [[@doctodo var_description:fieldHtmlOptions]]
     */
    public $fieldHtmlOptions = ['class' => 'form-group'];
    /**
     * @var [[@doctodo var_type:labelHtmlOptions]] [[@doctodo var_description:labelHtmlOptions]]
     */
    public $labelHtmlOptions = ['class' => 'control-label'];
    /**
     * @var [[@doctodo var_type:valueHtmlOptions]] [[@doctodo var_description:valueHtmlOptions]]
     */
    public $valueHtmlOptions = ['class' => 'form-control-static'];

    /**
     * @inheritdoc
     */
    public $gridClass = 'canis\web\grid\Grid';
    /**
     * @inheritdoc
     */
    public $gridCellClass = 'canis\web\grid\Cell';
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
        if (empty(Yii::$app->request->object)) {
            return false;
        }
        if (!($detailFields = Yii::$app->request->object->getDetailFields()) || empty($detailFields)) {
            return false;
        }
        $parts = [];
        $parts[] = Html::beginTag('div', $this->contentHtmlOptions);

        $grid = Yii::createObject(['class' => $this->gridClass]);
        // $parts[] = '<pre>'. print_r(array_keys($detailFields), true) .'</pre>';
        foreach ($detailFields as $key => $field) {
            $fieldHtmlOptions = $this->fieldHtmlOptions;
            $labelCell = $this->generateCell(Html::tag('label', $field->label, $this->labelHtmlOptions));
            $valueCell = $this->generateCell(Html::tag('p', $field->formattedValue, $this->valueHtmlOptions));
            $row = $grid->addRow([$labelCell, $valueCell]);

            if (true || $field->multiline) {
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
     * [[@doctodo method_description:generateCell]].
     *
     * @param [[@doctodo param_type:content]] $content [[@doctodo param_description:content]]
     *
     * @return [[@doctodo return_type:generateCell]] [[@doctodo return_description:generateCell]]
     */
    protected function generateCell($content)
    {
        return Yii::createObject(['class' => $this->gridCellClass, 'content' => $content, 'tabletSize' => false]);
    }

    /**
     * Get pagination settings.
     *
     * @return [[@doctodo return_type:getPaginationSettings]] [[@doctodo return_description:getPaginationSettings]]
     */
    public function getPaginationSettings()
    {
        return false;
    }
}
