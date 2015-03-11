<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\web\widgets;

use teal\base\collector\CollectedObjectTrait;
use Yii;

/**
 * Widget [[@doctodo class_description:cascade\components\web\widgets\Widget]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Widget extends BaseWidget implements \teal\base\WidgetInterface, \teal\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * @var [[@doctodo var_type:icon]] [[@doctodo var_description:icon]]
     */
    public $icon = false;
    /**
     * @var [[@doctodo var_type:section]] [[@doctodo var_description:section]]
     */
    public $section = false;
    /**
     * @inheritdoc
     */
    public $defaultDecoratorClass = 'cascade\components\web\widgets\decorator\PanelDecorator';
    /**
     * @var [[@doctodo var_type:gridCellClass]] [[@doctodo var_description:gridCellClass]]
     */
    public $gridCellClass = 'teal\web\grid\Cell';
    /**
     * @var [[@doctodo var_type:gridClass]] [[@doctodo var_description:gridClass]]
     */
    public $gridClass = 'teal\web\grid\Grid';

    /**
     * @inheritdoc
     */
    public $params = [];
    /**
     * @var [[@doctodo var_type:recreateParams]] [[@doctodo var_description:recreateParams]]
     */
    public $recreateParams = [];
    /**
     * @inheritdoc
     */
    public $htmlOptions = ['class' => 'ic-widget'];

    /**
     * @var [[@doctodo var_type:_widgetId]] [[@doctodo var_description:_widgetId]]
     */
    protected $_widgetId;
    /**
     * @var [[@doctodo var_type:_title]] [[@doctodo var_description:_title]]
     */
    protected $_title  = false;

    /**
     * @var [[@doctodo var_type:_lazy]] [[@doctodo var_description:_lazy]]
     */
    protected $_lazy  = false;

    /**
     * [[@doctodo method_description:stateKeyName]].
     *
     * @param [[@doctodo param_type:key]] $key [[@doctodo param_description:key]]
     *
     * @return [[@doctodo return_type:stateKeyName]] [[@doctodo return_description:stateKeyName]]
     */
    public function stateKeyName($key)
    {
        return 'widget.' . $this->systemId . '.' . $key;
    }

    /**
     * Get refresh instructions.
     *
     * @return [[@doctodo return_type:getRefreshInstructions]] [[@doctodo return_description:getRefreshInstructions]]
     */
    public function getRefreshInstructions()
    {
        $i = [];
        $i['type'] = 'widget';
        $i['systemId'] = $this->collectorItem->systemId;
        $i['recreateParams'] = $this->recreateParams;
        if ($this->section) {
            $i['section'] = $this->section->systemId;
        }

        return $i;
    }

    /**
     * Get lazy.
     *
     * @return [[@doctodo return_type:getLazy]] [[@doctodo return_description:getLazy]]
     */
    public function getLazy()
    {
        if (!Yii::$app->collectors['widgets']->lazy) {
            return false;
        }

        return $this->_lazy;
    }

    /**
     * Set lazy.
     *
     * @param [[@doctodo param_type:lazy]] $lazy [[@doctodo param_description:lazy]]
     */
    public function setLazy($lazy)
    {
        $this->_lazy = $lazy;
    }

    /**
     * Get state.
     *
     * @param [[@doctodo param_type:key]]     $key     [[@doctodo param_description:key]]
     * @param [[@doctodo param_type:default]] $default [[@doctodo param_description:default]] [optional]
     *
     * @return [[@doctodo return_type:getState]] [[@doctodo return_description:getState]]
     */
    public function getState($key, $default = null)
    {
        return Yii::$app->webState->get($this->stateKeyName($key), $default);
    }

    /**
     * Set state.
     *
     * @param [[@doctodo param_type:key]]   $key   [[@doctodo param_description:key]]
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @return [[@doctodo return_type:setState]] [[@doctodo return_description:setState]]
     */
    public function setState($key, $value)
    {
        return Yii::$app->webState->set($this->stateKeyName($key), $value);
    }

    /**
     * Get header menu.
     *
     * @return [[@doctodo return_type:getHeaderMenu]] [[@doctodo return_description:getHeaderMenu]]
     */
    public function getHeaderMenu()
    {
        return [];
    }

    /**
     * Get title.
     *
     * @return [[@doctodo return_type:getTitle]] [[@doctodo return_description:getTitle]]
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set title.
     *
     * @param [[@doctodo param_type:title]] $title [[@doctodo param_description:title]]
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * [[@doctodo method_description:ensureAssetBundles]].
     */
    public function ensureAssetBundles()
    {
        foreach ($this->assetBundles as $bundleClass) {
            $bundleClass::register(Yii::$app->view);
        }
    }

    /**
     * Get asset bundles.
     *
     * @return [[@doctodo return_type:getAssetBundles]] [[@doctodo return_description:getAssetBundles]]
     */
    public function getAssetBundles()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        Yii::beginProfile(get_called_class() . ':' . __FUNCTION__);
        $this->ensureAssetBundles();
        $this->ensureDecorator();
        $content = $this->generateContent();
        if ($content === false) {
            return;
        }
        if (($widgetAreas = $this->widgetAreas) && !empty($widgetAreas)) {
            $contentCell = ['class' => $this->gridCellClass, 'content' => $content];
            $contentRow = [Yii::createObject($contentCell)];
            $topRow = [];
            $bottomRow = [];
            foreach ($widgetAreas as $widgetArea) {
                if (!is_object($widgetArea)) {
                    $widgetArea = Yii::createObject($widgetArea);
                }
                $widgetArea->parentWidget = $this;
                $widgetAreaCell = $widgetArea->cell;
                if (!$widgetArea->isReady) {
                    continue;
                }
                switch ($widgetArea->location) {
                    case 'bottom':
                        array_push($bottomRow, $widgetAreaCell);
                    break;
                    case 'top':
                        array_push($topRow, $widgetAreaCell);
                    break;
                    case 'left':
                        array_unshift($contentRow, $widgetAreaCell);
                    break;
                    case 'right':
                        array_push($contentRow, $widgetAreaCell);
                    break;
                }
            }
            $grid = Yii::createObject(['class' => $this->gridClass]);
            if (!empty($topRow)) {
                $grid->addRow($topRow);
            }
            $grid->addRow($contentRow);
            if (!empty($bottomRow)) {
                $grid->addRow($bottomRow);
            }
            //\d($grid);exit;
            $content = $grid->generate();
        }
        $result = $this->generateStart() . $this->generateHeader() . $content . $this->generateFooter() . $this->generateEnd();
        Yii::endProfile(get_called_class() . ':' . __FUNCTION__);

        return $result;
    }

    /**
     * Get widget areas.
     *
     * @return [[@doctodo return_type:getWidgetAreas]] [[@doctodo return_description:getWidgetAreas]]
     */
    public function getWidgetAreas()
    {
        return [
        ];
    }

    /**
     * Get widget.
     *
     * @return unknown
     */
    public function getWidgetId()
    {
        if (!is_null($this->_widgetId)) {
            return $this->_widgetId;
        }

        return $this->_widgetId = 'ic-widget-' . md5(microtime() . mt_rand());
    }

    /**
     * @inheritdoc
     */
    public function setWidgetId($value)
    {
        $this->_widgetId = $value;
    }

    /**
     * Get priority adjust.
     *
     * @return [[@doctodo return_type:getPriorityAdjust]] [[@doctodo return_description:getPriorityAdjust]]
     */
    public function getPriorityAdjust()
    {
        return 0;
    }
}
