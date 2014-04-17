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
    public $producedWidgets = [];
    public $lastBuildId;

    public function getCollectorItemClass()
    {
        return '\cascade\components\web\widgets\Item';
    }

    public function getModulePrefix()
    {
        return 'Widget';
    }

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
     *
     *
     * @param  unknown $location
     * @param  unknown $owner    (optional)
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
