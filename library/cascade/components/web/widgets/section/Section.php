<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\section;

use Yii;

use infinite\helpers\ArrayHelper;
use infinite\helpers\Html;

/**
 * Section [@doctodo write class description for Section]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Section extends \cascade\components\web\widgets\Widget
{
    public $gridClass = 'infinite\\web\\grid\\Grid';
    public $defaultWidgetDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\PanelDecorator';
    public $section;

    /**
    * @inheritdoc
    **/
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

    public function getWidgetDecoratorClass()
    {
        return $this->defaultWidgetDecoratorClass;
    }

    public function generateStart()
    {
        $parts = [];
        $parts[] = Html::tag('div', '', ['id' => 'section-'.$this->systemId, 'class' => 'scroll-mark']);
        $parts[] = parent::generateStart();

        return implode('', $parts);
    }

    public function widgetCellSettings()
    {
        return [
            'mediumDesktopColumns' => 12,
            'tabletColumns' => 12,
            'baseSize' => 'tablet'
        ];
    }

    /**
    * @inheritdoc
    **/
    public function generateContent()
    {
        $items = [];
        foreach ($this->widgets as $widget) {
            $cell = Yii::$app->collectors['widgets']->build($this, $widget->object);
            if (!$cell) { \d($widget);exit; continue; }
            $items[] = $cell;
            Yii::configure($cell, $this->widgetCellSettings());

        }
        $grid = Yii::createObject(['class' => $this->gridClass, 'cells' => $items]);

        return $grid->generate();
    }

    public function getWidgets()
    {
        $widgets = $this->collectorItem->getAll();
        ArrayHelper::multisort($widgets, ['object.priority', 'object.name'], [SORT_ASC, SORT_ASC]);

        return $widgets;
    }

    public function defaultItems($parent = null)
    {
        return [];
    }
}
