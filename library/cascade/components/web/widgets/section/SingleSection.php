<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\section;

use Yii;

use infinite\helpers\Html;

/**
 * SingleSection [@doctodo write class description for SingleSection]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class SingleSection extends Section
{
    public $section;
    protected $_singleWidget;

    public function getCell()
    {
        $widgetCell = $this->singleWidget;
        if ($widgetCell) {
            $widgetCell->prepend(Html::tag('div', '', ['id' => 'section-'.$this->systemId, 'class' => 'scroll-mark']));

            return $widgetCell;
        }

        return false;
    }

    public function getSingleWidget()
    {
        if (is_null($this->_singleWidget)) {
            $this->_singleWidget = false;
            $widgets = $this->collectorItem->getAll();
            if (!empty($widgets)) {
                $widget = array_shift($widgets);
                $this->_singleWidget = Yii::$app->collectors['widgets']->build($this, $widget->object);
            }
        }

        return $this->_singleWidget;
    }

    /**
    * @inheritdoc
    **/
    public function getTitle()
    {
        $singleWidget = $this->singleWidget;
        if ($singleWidget && isset($singleWidget->content->panelTitle)) {
            return $singleWidget->content->panelTitle;
        }

        return parent::getTitle();
    }
}
