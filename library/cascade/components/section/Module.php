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
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }
}
