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
**/
abstract class Module extends \infinite\base\Module
{
    public $title;
    public $icon = 'ic-icon-info';
    public $priority = 1000; //lower is better

    public $widgetNamespace;

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent, $config=null)
    {
        Yii::$app->collectors->onAfterInit([$this, 'onAfterInit']);

        parent::__construct($id, $parent, $config);
    }

    public function getModuleType()
    {
        return 'Widget';
    }

    public function onAfterInit($event)
    {
        if (isset(Yii::$app->collectors['widgets']) and !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) { throw new Exception('Could not register widgets for '. $this->systemId .'!'); }
    }

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
                // 'title' => '%%type.'. $this->systemId .'.title.upperPlural%%'
            ];
            $summaryWidget['locations'] = ['front'];
            $summaryWidget['priority'] = $this->priority;
            $widgets[$id] = $summaryWidget;
        }
        //var_dump($widgets);exit;
        return $widgets;
    }

    public function getShortName()
    {
        preg_match('/Widget([A-Za-z]+)\\\Module/', get_class($this), $matches);
        if (!isset($matches[1])) {
            throw new Exception(get_class($this). " is not set up correctly!");
        }

        return $matches[1];
    }

}
