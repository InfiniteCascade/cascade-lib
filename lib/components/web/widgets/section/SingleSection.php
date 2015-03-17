<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets\section;

use canis\helpers\Html;
use Yii;

/**
 * SingleSection [[@doctodo class_description:cascade\components\web\widgets\section\SingleSection]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SingleSection extends Section
{
    /**
     * @inheritdoc
     */
    public $section;
    /**
     * @var [[@doctodo var_type:_singleWidget]] [[@doctodo var_description:_singleWidget]]
     */
    protected $_singleWidget;

    /**
     * Get cell.
     *
     * @return [[@doctodo return_type:getCell]] [[@doctodo return_description:getCell]]
     */
    public function getCell()
    {
        $widgetCell = $this->singleWidget;
        if ($widgetCell) {
            $widgetCell->prepend(Html::tag('div', '', ['id' => 'section-' . $this->systemId, 'class' => 'scroll-mark']));

            return $widgetCell;
        }

        return false;
    }

    /**
     * Get single widget.
     *
     * @return [[@doctodo return_type:getSingleWidget]] [[@doctodo return_description:getSingleWidget]]
     */
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
     */
    public function getTitle()
    {
        $singleWidget = $this->singleWidget;
        if ($singleWidget && isset($singleWidget->content->panelTitle)) {
            return $singleWidget->content->panelTitle;
        }

        return parent::getTitle();
    }
}
