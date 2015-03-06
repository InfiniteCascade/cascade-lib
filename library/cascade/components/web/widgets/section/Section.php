<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\section;

use infinite\helpers\ArrayHelper;
use infinite\helpers\Html;
use Yii;

/**
 * Section [[@doctodo class_description:cascade\components\web\widgets\section\Section]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Section extends \cascade\components\web\widgets\Widget
{
    /**
     * @inheritdoc
     */
    public $gridClass = 'infinite\web\grid\Grid';
    /**
     * @var [[@doctodo var_type:defaultWidgetDecoratorClass]] [[@doctodo var_description:defaultWidgetDecoratorClass]]
     */
    public $defaultWidgetDecoratorClass = 'cascade\components\web\widgets\decorator\PanelDecorator';
    /**
     * @inheritdoc
     */
    public $section;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (isset($this->section)) {
            $this->icon = $this->section->icon;
            $this->title = $this->section->sectionTitle;
        }
        if (isset($this->collectorItem)) {
            $this->collectorItem->registerMultiple($this, $this->defaultItems());
        }
    }

    /**
     * Get widget decorator class.
     *
     * @return [[@doctodo return_type:getWidgetDecoratorClass]] [[@doctodo return_description:getWidgetDecoratorClass]]
     */
    public function getWidgetDecoratorClass()
    {
        return $this->defaultWidgetDecoratorClass;
    }

    /**
     * [[@doctodo method_description:generateStart]].
     *
     * @return [[@doctodo return_type:generateStart]] [[@doctodo return_description:generateStart]]
     */
    public function generateStart()
    {
        $parts = [];
        $parts[] = Html::tag('div', '', ['id' => 'section-' . $this->systemId, 'class' => 'scroll-mark']);
        $parts[] = parent::generateStart();

        return implode('', $parts);
    }

    /**
     * [[@doctodo method_description:widgetCellSettings]].
     *
     * @return [[@doctodo return_type:widgetCellSettings]] [[@doctodo return_description:widgetCellSettings]]
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
     * @inheritdoc
     */
    public function generateContent()
    {
        $items = [];
        foreach ($this->widgets as $widget) {
            $cell = Yii::$app->collectors['widgets']->build($this, $widget->object);
            if (!$cell) {
                \d($widget);
                exit;
                continue;
            }
            $items[] = $cell;
            Yii::configure($cell, $this->widgetCellSettings());
        }
        $grid = Yii::createObject(['class' => $this->gridClass, 'cells' => $items]);

        return $grid->generate();
    }

    /**
     * Get widgets.
     *
     * @return [[@doctodo return_type:getWidgets]] [[@doctodo return_description:getWidgets]]
     */
    public function getWidgets()
    {
        $widgets = $this->collectorItem->getAll();
        ArrayHelper::multisort($widgets, ['object.priority', 'object.name'], [SORT_ASC, SORT_ASC]);

        return $widgets;
    }

    /**
     * [[@doctodo method_description:defaultItems]].
     *
     * @param [[@doctodo param_type:parent]] $parent [[@doctodo param_description:parent]] [optional]
     *
     * @return [[@doctodo return_type:defaultItems]] [[@doctodo return_description:defaultItems]]
     */
    public function defaultItems($parent = null)
    {
        return [];
    }
}
