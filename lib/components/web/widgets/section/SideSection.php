<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets\section;

use cascade\components\web\widgets\base\Header as WidgetHeader;
use cascade\components\web\widgets\decorator\EmbeddedDecorator;
use cascade\components\web\widgets\Item as WidgetItem;

/**
 * SideSection [[@doctodo class_description:cascade\components\web\widgets\section\SideSection]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SideSection extends Section
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = false;
        $this->icon = false;
    }

    /**
     * @inheritdoc
     */
    public function getWidgetDecoratorClass()
    {
        return EmbeddedDecorator::className();
    }

    /**
     * @inheritdoc
     */
    public function widgetCellSettings()
    {
        return [
            'mediumDesktopColumns' => 12,
            'tabletColumns' => 12,
            'baseSize' => 'tablet',
        ];
    }

    /**
     * [[@doctodo method_description:isSingle]].
     *
     * @return [[@doctodo return_type:isSingle]] [[@doctodo return_description:isSingle]]
     */
    public function isSingle()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function defaultItems($parent = null)
    {
        $default = [];
        $default['_header'] = [
            'object' => [
                'class' => WidgetItem::className(),
                'widget' => ['class' => WidgetHeader::className()],
            ],
        ];

        return $default;
    }
}
