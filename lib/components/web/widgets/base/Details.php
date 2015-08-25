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
    public $contentHtmlOptions = [];
    /**
     * @var [[@doctodo var_type:labelHtmlOptions]] [[@doctodo var_description:labelHtmlOptions]]
     */
    public $termHtmlOptions = [];
    /**
     * @var [[@doctodo var_type:valueHtmlOptions]] [[@doctodo var_description:valueHtmlOptions]]
     */
    public $valueHtmlOptions = [];

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
        Html::addCssClass($this->contentHtmlOptions, 'dl-horizontal');
        $parts[] = Html::beginTag('dl', $this->contentHtmlOptions);

        foreach ($detailFields as $key => $field) {
            $parts[] = Html::tag('dt', $field->label, $this->termHtmlOptions);
            $parts[] = Html::tag('dd', $field->formattedValue, $this->valueHtmlOptions);

        }
        $parts[] = Html::endTag('dl');

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
