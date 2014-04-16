<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;
use yii\helpers\Url;

use infinite\helpers\Html;

class SimpleLinkList extends BaseList
{
    public $renderPager = false;

    public function getGridCellSettings()
    {
        return [
            'columns' => 3,
            'maxColumns' => 3,
            'tabletSize' => false
        ];
    }

    public function getListOptions()
    {
        return array_merge(parent::getListOptions(), ['tag' => 'div']);
    }

    public function getListItemOptions($model, $key, $index)
    {
        $options = array_merge(parent::getListItemOptions($model, $key, $index), ['tag' => 'a', 'href' => Url::to($model->getUrl('view'))]);
        if (!$model->can('read')) {
            Html::addCssClass($options, 'disabled');
            $options['href'] = '#';
        }

        return $options;
    }

    public function getMenuItems($model, $key, $index)
    {
        return [];
    }

    public function contentTemplate($model)
    {
        return [
            'descriptor' => ['class' => 'list-group-item-heading', 'tag' => 'h5']
        ];
    }

    public function renderItemContent($model, $key, $index)
    {
        return parent::renderItemContent($model, $key, $index);
    }
}
