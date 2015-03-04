<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\section;

use infinite\base\collector\Item as BaseItem;

/**
 * Module [@doctodo write class description for Module].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\base\CollectorModule implements SectionInterface
{
    use SectionTrait;

    /**
     * @var __var_version_type__ __var_version_description__
     */
    public $version = 1;
    /**
     * @var __var_priority_type__ __var_priority_description__
     */
    protected $_priority = 1000; //lower is better

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Section';
    }

    /**
     * @inheritdoc
     */
    public function getCollectorName()
    {
        return 'sections';
    }

    /**
     * Set title.
     *
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setTitle($value)
    {
        $this->_title = $value;
    }

    /**
     * @inheritdoc
     */
    public function getCollectedObject(BaseItem $item)
    {
        $widget = $this->widget;
        $widget->collectorItem = $this->collectorItem = $item;
        $this->collectorItem->priority = $this->priority;
        $this->collectorItem->title = $this->_title;

        return $widget;
    }

    /**
     * Get priority.
     *
     * @return __return_getPriority_type__ __return_getPriority_description__
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     * Set priority.
     *
     * @param __param_priority_type__ $priority __param_priority_description__
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }
}
