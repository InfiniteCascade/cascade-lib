<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use Yii;

use infinite\base\exceptions\Exception;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     * @var __var_title_type__ __var_title_description__
     */
    public $title;
    /**
     * @var __var_icon_type__ __var_icon_description__
     */
    public $icon = 'ic-icon-info';
    /**
     * @var __var_priority_type__ __var_priority_description__
     */
    public $priority = 1000; //lower is better

    public $locations = []; //lower is better

    /**
     * @var __var_widgetNamespace_type__ __var_widgetNamespace_description__
     */
    public $widgetNamespace;


    public function getCollectorName()
    {
        return false;
    }

    /**
    * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Widget';
    }

    /**
     * __method_onAfterInit_description__
     * @param __param_event_type__ $event __param_event_description__
     * @throws Exception __exception_Exception_description__
     */
    public function onAfterInit($event)
    {
        if (isset(Yii::$app->collectors['widgets']) and !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) { throw new Exception('Could not register widgets for '. $this->systemId .'!'); }
        return parent::onAfterInit($event);
    }

    /**
     * __method_widgets_description__
     * @return __return_widgets_type__ __return_widgets_description__
     */
    public function widgets()
    {
        $widgets = [];
        $className = $this->widgetNamespace .'\\'. 'Content';
        @class_exists($className);
        if (class_exists($className, false)) {
            $summaryWidget = [];
            $id = $this->systemId .'Content';
            $summaryWidget['widget'] = [
                'class' => $className,
                'icon' => $this->icon,
                'owner' => $this
            ];
            $summaryWidget['locations'] = $this->locations;
            $summaryWidget['priority'] = $this->priority;
            $widgets[$id] = $summaryWidget;
        }
        //\d($widgets);exit;
        return $widgets;
    }

    /**
     * Get short name
     * @return __return_getShortName_type__ __return_getShortName_description__
     * @throws Exception __exception_Exception_description__
     */
    public function getShortName()
    {
        preg_match('/Widget([A-Za-z]+)\\\Module/', get_class($this), $matches);
        if (!isset($matches[1])) {
            throw new Exception(get_class($this). " is not set up correctly!");
        }

        return $matches[1];
    }

}
