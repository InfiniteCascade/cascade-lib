<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use Yii;

use infinite\base\collector\CollectedObjectTrait;

/**
 * Item [@doctodo write class description for Item]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Item extends \infinite\base\collector\Item implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * @var __var_name_type__ __var_name_description__
     */
    public $name;
    /**
     * @var __var_widget_type__ __var_widget_description__
     */
    public $widget;
    /**
     * @var __var_tab_type__ __var_tab_description__
     */
    public $tab;
    /**
     * @var __var_priority_type__ __var_priority_description__
     */
    public $priority = 0;
    /**
     * @var __var_locations_type__ __var_locations_description__
     */
    public $locations = [];
    /**
     * @var __var__section_type__ __var__section_description__
     */
    protected $_section;
    /**
     * @var __var_settings_type__ __var_settings_description__
     */
    public $settings = [];

    /**
    * @inheritdoc
    **/
    public function getObject()
    {
        if (is_null($this->widget)) {
            return null;
        }
        $object = Yii::createObject($this->widget);
        $object->settings = $this->settings;
        $object->collectorItem = $this;

        return $object;
    }

    /**
     * __method_getSection_description__
     * @param  __param_parent_type__      $parent   __param_parent_description__ [optional]
     * @param  array                      $settings __param_settings_description__ [optional]
     * @return __return_getSection_type__ __return_getSection_description__
     */
    public function getSection($parent = null, $settings = [])
    {
        $settings = array_merge($this->settings, $settings);
        if (is_null($this->_section)) {
            $this->_section = $this->owner->getSection($parent, $settings);
        }
        if (is_callable($this->_section) || (is_array($this->_section) && !empty($this->_section[0]) && is_object($this->_section[0]))) {
            return $this->evaluateExpression($this->_section, ['parent' => $parent, 'settings' => $settings]);
        }

        return $this->_section;
    }

    /**
     * __method_setSection_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setSection($value)
    {
        $this->_section = $value;
    }

}
