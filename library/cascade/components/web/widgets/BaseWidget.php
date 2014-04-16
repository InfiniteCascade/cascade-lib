<?php
/**
 * ./app/components/web/widgets/RBaseWidget.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\web\widgets;

use Yii;

use cascade\components\helpers\StringHelper;

use infinite\base\ObjectTrait;
use infinite\base\ComponentTrait;
use infinite\web\grid\CellContentTrait;
use infinite\web\RenderTrait;

abstract class BaseWidget extends \yii\bootstrap\Widget
{
    use ObjectTrait;
    use ComponentTrait;
    use CellContentTrait;
    use RenderTrait;

    public $owner;
    public $instanceSettings;

    public $defaultDecoratorClass = 'cascade\\components\\web\\widgets\\decorator\\PanelDecorator';

    public $params = [];
    public $htmlOptions = [];

    protected $_systemId;
    protected $_settings;
    protected $_decorator;

    abstract public function generateContent();
    abstract public function generate();

    public function ensureDecorator()
    {
        if (!$this->hasDecorator()) {
            $this->attachDecorator($this->defaultDecoratorClass);
        }
    }

    public function hasDecorator()
    {
        return $this->_decorator !== null;
    }

    public function attachDecorator($decorator)
    {
        if ($this->hasDecorator()) {
            $this->detachBehavior('__decorator');
        }

        return $this->_decorator = $this->attachBehavior('__decorator', ['class' => $decorator]);
    }

    public function behaviors()
    {
        return [
            'CellBehavior' => [
                'class' => 'cascade\\components\\web\\widgets\\base\\CellBehavior'
            ]
        ];
    }

    public function output()
    {
        echo $this->generate();
    }

    public function run()
    {
        echo $this->generate();
    }

    public function parseText($text)
    {
        return StringHelper::parseText($text, $this->variables);
    }

    public function getVariables()
    {
        return [];
    }

    /**
     *
     *
     * @return unknown
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     *
     *
     * @param unknown $state
     */
    public function setSettings($settings)
    {
        $this->_settings = $settings;
    }

    /**
     *
     *
     * @return unknown
     */
    public function getWidgetId()
    {
        if (!is_null($this->_widgetId)) {
            return $this->_widgetId;
        }

        return $this->_widgetId = 'ic-widget-'. md5(microtime() . mt_rand());
    }

    public function setWidgetId($value)
    {
        $this->_widgetId = $value;
    }

    /**
     *
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

    public function setSystemId($value)
    {
        $this->_systemId = $value;
    }
}
