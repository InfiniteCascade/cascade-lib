<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\widgets;

use cascade\components\helpers\StringHelper;
use canis\base\ComponentTrait;
use canis\base\ObjectTrait;
use canis\web\grid\CellContentTrait;
use canis\web\RenderTrait;
use Yii;

/**
 * BaseWidget [[@doctodo class_description:cascade\components\web\widgets\BaseWidget]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class BaseWidget extends \yii\bootstrap\Widget
{
    use ObjectTrait;
    use ComponentTrait;
    use CellContentTrait;
    use RenderTrait;

    /**
     * @var [[@doctodo var_type:owner]] [[@doctodo var_description:owner]]
     */
    public $owner;
    /**
     * @var [[@doctodo var_type:instanceSettings]] [[@doctodo var_description:instanceSettings]]
     */
    public $instanceSettings;

    /**
     * @var [[@doctodo var_type:defaultDecoratorClass]] [[@doctodo var_description:defaultDecoratorClass]]
     */
    public $defaultDecoratorClass = 'cascade\components\web\widgets\decorator\PanelDecorator';

    /**
     * @var [[@doctodo var_type:params]] [[@doctodo var_description:params]]
     */
    public $params = [];
    /**
     * @var [[@doctodo var_type:htmlOptions]] [[@doctodo var_description:htmlOptions]]
     */
    public $htmlOptions = [];

    /**
     * @var [[@doctodo var_type:_systemId]] [[@doctodo var_description:_systemId]]
     */
    protected $_systemId;
    /**
     * @var [[@doctodo var_type:_settings]] [[@doctodo var_description:_settings]]
     */
    protected $_settings;
    /**
     * @var [[@doctodo var_type:_decorator]] [[@doctodo var_description:_decorator]]
     */
    protected $_decorator;

    /**
     * [[@doctodo method_description:generateContent]].
     */
    abstract public function generateContent();
    /**
     * [[@doctodo method_description:generate]].
     */
    abstract public function generate();

    /**
     * [[@doctodo method_description:ensureDecorator]].
     */
    public function ensureDecorator()
    {
        if (!$this->hasDecorator()) {
            $this->attachDecorator($this->defaultDecoratorClass);
        }
    }

    /**
     * [[@doctodo method_description:hasDecorator]].
     *
     * @return [[@doctodo return_type:hasDecorator]] [[@doctodo return_description:hasDecorator]]
     */
    public function hasDecorator()
    {
        return $this->_decorator !== null;
    }

    /**
     * [[@doctodo method_description:attachDecorator]].
     *
     * @param [[@doctodo param_type:decorator]] $decorator [[@doctodo param_description:decorator]]
     *
     * @return [[@doctodo return_type:attachDecorator]] [[@doctodo return_description:attachDecorator]]
     */
    public function attachDecorator($decorator)
    {
        if ($this->hasDecorator()) {
            $this->detachBehavior('__decorator');
        }

        return $this->_decorator = $this->attachBehavior('__decorator', ['class' => $decorator]);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'CellBehavior' => [
                'class' => 'cascade\components\web\widgets\base\CellBehavior',
            ],
        ];
    }

    /**
     * [[@doctodo method_description:output]].
     */
    public function output()
    {
        echo $this->generate();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo $this->generate();
    }

    /**
     * [[@doctodo method_description:parseText]].
     *
     * @param [[@doctodo param_type:text]] $text [[@doctodo param_description:text]]
     *
     * @return [[@doctodo return_type:parseText]] [[@doctodo return_description:parseText]]
     */
    public function parseText($text)
    {
        return StringHelper::parseText($text, $this->variables);
    }

    /**
     * Get variables.
     *
     * @return [[@doctodo return_type:getVariables]] [[@doctodo return_description:getVariables]]
     */
    public function getVariables()
    {
        return [];
    }

    /**
     * Get settings.
     *
     * @return unknown
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * Set settings.
     *
     * @param [[@doctodo param_type:settings]] $settings [[@doctodo param_description:settings]]
     */
    public function setSettings($settings)
    {
        $this->_settings = $settings;
    }

    /**
     * Get widget.
     *
     * @return unknown
     */
    public function getWidgetId()
    {
        if (!is_null($this->_widgetId)) {
            return $this->_widgetId;
        }

        return $this->_widgetId = 'ic-widget-' . md5(microtime() . mt_rand());
    }

    /**
     * Set widget.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setWidgetId($value)
    {
        $this->_widgetId = $value;
    }

    /**
     * Get system.
     *
     * @return unknown
     */
    public function getSystemId()
    {
        if (!isset($this->_systemId)) {
            if (isset($this->collectorItem) && isset($this->collectorItem->systemId)) {
                $this->_systemId = $this->collectorItem->systemId;
            }
        }

        return $this->_systemId;
    }

    /**
     * Set system.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setSystemId($value)
    {
        $this->_systemId = $value;
    }
}
