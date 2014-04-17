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

    public $icon = false;
    public $section = false;
    public $defaultDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\PanelDecorator';
    public $gridCellClass = 'infinite\web\grid\Cell';
    public $gridClass = 'infinite\web\grid\Grid';

    public $params = [];
    public $recreateParams = [];
    public $htmlOptions = ['class' => 'ic-widget'];

    protected $_widgetId;
    protected $_title  = false;

    public function stateKeyName($key)
    {
        return 'widget.'.$this->systemId . '.'. $key;
    }

    public function getState($key, $default = null)
    {
        return Yii::$app->webState->get($this->stateKeyName($key), $default);
    }

    public function setState($key, $value)
    {
        return Yii::$app->webState->set($this->stateKeyName($key), $value);
    }

    public function getHeaderMenu()
    {
        return [];
    }

    public function getTitle()
    {
        return $this->_title;
    }

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

    public function getWidgetAreas()
    {
        return [
        ];
    }

    /**
     *
     *
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
