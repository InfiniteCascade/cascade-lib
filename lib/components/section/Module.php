<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\section;

use canis\base\collector\Item as BaseItem;

/**
 * Module [[@doctodo class_description:cascade\components\section\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\base\CollectorModule implements SectionInterface
{
    use SectionTrait;

    /**
     * @var [[@doctodo var_type:version]] [[@doctodo var_description:version]]
     */
    public $version = 1;

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
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
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
     * @return [[@doctodo return_type:getPriority]] [[@doctodo return_description:getPriority]]
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     * Set priority.
     *
     * @param [[@doctodo param_type:priority]] $priority [[@doctodo param_description:priority]]
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }
}
