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

/**
 * SimpleLinkList [@doctodo write class description for SimpleLinkList]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SimpleLinkList extends BaseList
{
    /**
     * @inheritdoc
     */
    public $renderPager = false;

    /**
     * Get grid cell settings
     * @return __return_getGridCellSettings_type__ __return_getGridCellSettings_description__
     */
    public function getGridCellSettings()
    {
        return [
            'columns' => 3,
            'maxColumns' => 6,
            'tabletSize' => false
        ];
    }

    /**
    * @inheritdoc
     */
    public function getListOptions()
    {
        return array_merge(parent::getListOptions(), ['tag' => 'div']);
    }

    /**
    * @inheritdoc
     */
    public function getListItemOptions($model, $key, $index)
    {
        $options = array_merge(parent::getListItemOptions($model, $key, $index), ['tag' => 'a', 'href' => Url::to($model->getUrl('view'))]);
        if (!$model->can('read')) {
            Html::addCssClass($options, 'disabled');
            $options['href'] = '#';
        }

        return $options;
    }


    public function getHeaderMenu()
    {
        $menu = [];
        if (Yii::$app->gk->canGeneral('create', $this->owner->primaryModel)) {
            $menu['create'] = [
                'icon' => 'fa fa-plus',
                //'label' => 'Create',
                'label' => '<i class="fa fa-plus" title="Create"></i>',
                'url' => ['object/create', 'type' => $this->owner->systemId],
                'linkOptions' => ['data-handler' => 'background']
            ];
        }
        return $menu;
    }

    /**
    * @inheritdoc
     */
    public function getMenuItems($model, $key, $index)
    {
        $menu = [];
        return $menu;
    }

    /**
    * @inheritdoc
     */
    public function contentTemplate($model)
    {
        return [
            'descriptor' => ['class' => 'list-group-item-heading', 'tag' => 'h5']
        ];
    }

    /**
    * @inheritdoc
     */
    public function renderItemContent($model, $key, $index)
    {
        return parent::renderItemContent($model, $key, $index);
    }
}
