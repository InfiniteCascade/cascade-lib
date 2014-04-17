<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use Yii;
use cascade\components\web\widgets\section\Section;

/**
 * Collector [@doctodo write class description for Collector]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Collector extends \infinite\base\collector\Module
{
    /**
     * @var __var_producedWidgets_type__ __var_producedWidgets_description__
     */
    public $producedWidgets = [];
    /**
     * @var __var_lastBuildId_type__ __var_lastBuildId_description__
     */
    public $lastBuildId;

    /**
    * @inheritdoc
    **/
    public function getCollectorItemClass()
    {
        return '\cascade\components\web\widgets\Item';
    }

    /**
    * @inheritdoc
    **/
    public function getModulePrefix()
    {
        return 'Widget';
    }

    /**
     * __method_build_description__
     * @param cascade\components\web\widgets\section\Section $section          __param_section_description__
     * @param __param_widgetName_type__                      $widgetName       __param_widgetName_description__
     * @param array                                          $instanceSettings __param_instanceSettings_description__ [optional]
     * @return __return_build_type__                          __return_build_description__
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
     * __method_getLocation_description__
     * @param unknown $location
     * @param unknown $owner    (optional)
     * @return unknown
     */
    public function getLocation($location, $owner = null)
    {
        $bucket = $this->getBucket('locations:'.$location);
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
