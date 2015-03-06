<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use infinite\base\exceptions\Exception;
use Yii;

/**
 * Module [[@doctodo class_description:cascade\components\web\widgets\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     * @var [[@doctodo var_type:title]] [[@doctodo var_description:title]]
     */
    public $title;
    /**
     * @var [[@doctodo var_type:icon]] [[@doctodo var_description:icon]]
     */
    public $icon = 'ic-icon-info';
    /**
     * @var [[@doctodo var_type:priority]] [[@doctodo var_description:priority]]
     */
    public $priority = 1000; //lower is better

    /**
     * @var [[@doctodo var_type:locations]] [[@doctodo var_description:locations]]
     */
    public $locations = []; //lower is better

    /**
     * @var [[@doctodo var_type:widgetNamespace]] [[@doctodo var_description:widgetNamespace]]
     */
    public $widgetNamespace;

    /**
     * @inheritdoc
     */
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
     * [[@doctodo method_description:onAfterInit]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]]
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:onAfterInit]] [[@doctodo return_description:onAfterInit]]
     *
     */
    public function onAfterInit($event)
    {
        if (isset(Yii::$app->collectors['widgets']) and !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) {
            throw new Exception('Could not register widgets for ' . $this->systemId . '!');
        }

        return parent::onAfterInit($event);
    }

    /**
     * [[@doctodo method_description:widgets]].
     *
     * @return [[@doctodo return_type:widgets]] [[@doctodo return_description:widgets]]
     */
    public function widgets()
    {
        $widgets = [];
        $className = $this->widgetNamespace . '\\' . 'Content';
        @class_exists($className);
        if (class_exists($className, false)) {
            $summaryWidget = [];
            $id = $this->systemId . 'Content';
            $summaryWidget['widget'] = [
                'class' => $className,
                'icon' => $this->icon,
                'owner' => $this,
            ];
            $summaryWidget['locations'] = $this->locations;
            $summaryWidget['priority'] = $this->priority;
            $widgets[$id] = $summaryWidget;
        }
        //\d($widgets);exit;
        return $widgets;
    }

    /**
     * Get short name.
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:getShortName]] [[@doctodo return_description:getShortName]]
     *
     */
    public function getShortName()
    {
        preg_match('/Widget([A-Za-z]+)\\\Module/', get_class($this), $matches);
        if (!isset($matches[1])) {
            throw new Exception(get_class($this) . " is not set up correctly!");
        }

        return $matches[1];
    }
}
