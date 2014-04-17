<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use Yii;

use cascade\components\helpers\StringHelper;

use infinite\base\ObjectTrait;
use infinite\base\ComponentTrait;
use infinite\web\grid\CellContentTrait;
use infinite\web\RenderTrait;

/**
 * BaseWidget [@doctodo write class description for BaseWidget]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class BaseWidget extends \yii\bootstrap\Widget
{
    use ObjectTrait;
    use ComponentTrait;
    use CellContentTrait;
    use RenderTrait;

    /**
     * @var __var_owner_type__ __var_owner_description__
     */
    public $owner;
    /**
     * @var __var_instanceSettings_type__ __var_instanceSettings_description__
     */
    public $instanceSettings;

    /**
     * @var __var_defaultDecoratorClass_type__ __var_defaultDecoratorClass_description__
     */
    public $defaultDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\PanelDecorator';

    /**
     * @var __var_params_type__ __var_params_description__
     */
    public $params = [];
    /**
     * @var __var_htmlOptions_type__ __var_htmlOptions_description__
     */
    public $htmlOptions = [];

    /**
     * @var __var__systemId_type__ __var__systemId_description__
     */
    protected $_systemId;
    /**
     * @var __var__settings_type__ __var__settings_description__
     */
    protected $_settings;
    /**
     * @var __var__decorator_type__ __var__decorator_description__
     */
    protected $_decorator;

    /**
     * __method_generateContent_description__
     */
    abstract public function generateContent();
    /**
     * __method_generate_description__
     */
    abstract public function generate();

    /**
     * __method_ensureDecorator_description__
     */
    public function ensureDecorator()
    {
        if (!$this->hasDecorator()) {
            $this->attachDecorator($this->defaultDecoratorClass);
        }
    }

    /**
     * __method_hasDecorator_description__
     * @return __return_hasDecorator_type__ __return_hasDecorator_description__
     */
    public function hasDecorator()
    {
        return $this->_decorator !== null;
    }

    /**
     * __method_attachDecorator_description__
     * @param __param_decorator_type__ $decorator __param_decorator_description__
     * @return __return_attachDecorator_type__ __return_attachDecorator_description__
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
    **/
    public function behaviors()
    {
        return [
            'CellBehavior' => [
                'class' => 'cascade\\components\\web\\widgets\\base\\CellBehavior'
            ]
        ];
    }

    /**
     * __method_output_description__
     */
    public function output()
    {
        echo $this->generate();
    }

    /**
    * @inheritdoc
    **/
    public function run()
    {
        echo $this->generate();
    }

    /**
     * __method_parseText_description__
     * @param __param_text_type__ $text __param_text_description__
     * @return __return_parseText_type__ __return_parseText_description__
     */
    public function parseText($text)
    {
        return StringHelper::parseText($text, $this->variables);
    }

    /**
     * __method_getVariables_description__
     * @return __return_getVariables_type__ __return_getVariables_description__
     */
    public function getVariables()
    {
        return [];
    }

    /**
     * __method_getSettings_description__
     * @return unknown
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * __method_setSettings_description__
     * @param __param_settings_type__ $settings __param_settings_description__
     */
    public function setSettings($settings)
    {
        $this->_settings = $settings;
    }

    /**
     * __method_getWidgetId_description__
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
     * __method_setWidgetId_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setWidgetId($value)
    {
        $this->_widgetId = $value;
    }

    /**
     * __method_getSystemId_description__
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
     * __method_setSystemId_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setSystemId($value)
    {
        $this->_systemId = $value;
    }
}
