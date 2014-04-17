<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use Yii;

use infinite\base\collector\CollectedObjectTrait;

/**
 * Widget [@doctodo write class description for Widget]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class Widget extends BaseWidget implements \infinite\base\WidgetInterface, \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * @var __var_icon_type__ __var_icon_description__
     */
    public $icon = false;
    /**
     * @var __var_section_type__ __var_section_description__
     */
    public $section = false;
    /**
     * @inheritdoc
     */
    public $defaultDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\PanelDecorator';
    /**
     * @var __var_gridCellClass_type__ __var_gridCellClass_description__
     */
    public $gridCellClass = 'infinite\web\grid\Cell';
    /**
     * @var __var_gridClass_type__ __var_gridClass_description__
     */
    public $gridClass = 'infinite\web\grid\Grid';

    /**
     * @inheritdoc
     */
    public $params = [];
    /**
     * @var __var_recreateParams_type__ __var_recreateParams_description__
     */
    public $recreateParams = [];
    /**
     * @inheritdoc
     */
    public $htmlOptions = ['class' => 'ic-widget'];

    /**
     * @var __var__widgetId_type__ __var__widgetId_description__
     */
    protected $_widgetId;
    /**
     * @var __var__title_type__ __var__title_description__
     */
    protected $_title  = false;

    /**
     * __method_stateKeyName_description__
     * @param __param_key_type__           $key __param_key_description__
     * @return __return_stateKeyName_type__ __return_stateKeyName_description__
     */
    public function stateKeyName($key)
    {
        return 'widget.'.$this->systemId . '.'. $key;
    }

    /**
     * __method_getState_description__
     * @param __param_key_type__       $key     __param_key_description__
     * @param __param_default_type__   $default __param_default_description__ [optional]
     * @return __return_getState_type__ __return_getState_description__
     */
    public function getState($key, $default = null)
    {
        return Yii::$app->webState->get($this->stateKeyName($key), $default);
    }

    /**
     * __method_setState_description__
     * @param __param_key_type__       $key   __param_key_description__
     * @param __param_value_type__     $value __param_value_description__
     * @return __return_setState_type__ __return_setState_description__
     */
    public function setState($key, $value)
    {
        return Yii::$app->webState->set($this->stateKeyName($key), $value);
    }

    /**
     * __method_getHeaderMenu_description__
     * @return __return_getHeaderMenu_type__ __return_getHeaderMenu_description__
     */
    public function getHeaderMenu()
    {
        return [];
    }

    /**
     * __method_getTitle_description__
     * @return __return_getTitle_type__ __return_getTitle_description__
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * __method_setTitle_description__
     * @param __param_title_type__ $title __param_title_description__
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
    * @inheritdoc
    **/
    public function generate()
    {
        Yii::beginProfile(get_called_class() .':'. __FUNCTION__);
        $this->ensureDecorator();
        $content = $this->generateContent();
        if ($content === false) { return; }
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
                if (!$widgetArea->isReady) { continue; }
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
        Yii::endProfile(get_called_class() .':'. __FUNCTION__);

        return $result;
    }

    /**
     * __method_getWidgetAreas_description__
     * @return __return_getWidgetAreas_type__ __return_getWidgetAreas_description__
     */
    public function getWidgetAreas()
    {
        return [
        ];
    }

    /**
     * __method_getWidgetId_description__
     * @return unknown
     */
    public function getWidgetId()
    {
        if (!is_null($this->_widgetId)) {
            return $this->_widgetId;
        }

        return $this->_widgetId = 'ic-widget-'. md5(microtime() . mt_rand());
    }

    /**
    * @inheritdoc
    **/
    public function setWidgetId($value)
    {
        $this->_widgetId = $value;
    }

}
