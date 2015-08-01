<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\widgets;

use cascade\components\web\widgets\section\Section;
use Yii;

/**
 * Collector [[@doctodo class_description:cascade\components\web\widgets\Collector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \canis\base\collector\Module
{
    /**
     * @var [[@doctodo var_type:producedWidgets]] [[@doctodo var_description:producedWidgets]]
     */
    public $producedWidgets = [];
    /**
     * @var [[@doctodo var_type:lastBuildId]] [[@doctodo var_description:lastBuildId]]
     */
    public $lastBuildId;

    /**
     * @var [[@doctodo var_type:_lazy]] [[@doctodo var_description:_lazy]]
     */
    protected $_lazy  = false;

    /**
     * Get lazy.
     *
     * @return [[@doctodo return_type:getLazy]] [[@doctodo return_description:getLazy]]
     */
    public function getLazy()
    {
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
     * @inheritdoc
     */
    public function getCollectorItemClass()
    {
        return Item::className();
    }

    /**
     * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Widget';
    }

    /**
     * [[@doctodo method_description:build]].
     *
     * @param cascade\components\web\widgets\section\Section $section          [[@doctodo param_description:section]]
     * @param [[@doctodo param_type:widgetName]]             $widgetName       [[@doctodo param_description:widgetName]]
     * @param array                                          $instanceSettings [[@doctodo param_description:instanceSettings]] [optional]
     *
     * @return [[@doctodo return_type:build]] [[@doctodo return_description:build]]
     */
    public function build(Section $section = null, $widgetName, $instanceSettings = [])
    {
        if (is_object($widgetName)) {
            $widget = $widgetName;
        } else {
            $widget = $this->getOne($widgetName);
        }
        $widgetObject = $widget->object;
        if (is_null($widgetObject)) {
            return false;
        }
        if (isset($section)) {
            $widgetObject->attachDecorator($section->widgetDecoratorClass);
            $widgetObject->section = $section;
        }
        $widgetObject->owner = $widget->owner;
        Yii::configure($widgetObject, $instanceSettings);
        $cell = $widgetObject->cell;

        $this->lastBuildId = $widgetObject->getWidgetId();
        $this->producedWidgets[$widgetObject->widgetId] = ['widget' => $widgetObject->systemId, 'id' => $widgetObject->widgetId, 'params' => $widgetObject->recreateParams];

        return $cell;
    }

    /**
     * Get location.
     *
     * @param unknown $location
     * @param unknown $owner    (optional)
     *
     * @return unknown
     */
    public function getLocation($location, $owner = null)
    {
        $bucket = $this->getBucket('locations:' . $location);
        if (is_null($owner)) {
            return $bucket->toArray();
        } else {
            $result = [];
            foreach ($bucket as $key => $widget) {
                if ($widget->owner === $owner) {
                    $result[$key] = $widget;
                }
            }

            return $result;
        }
    }
}
