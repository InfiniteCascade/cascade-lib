<?php
/**
 * ./app/components/sections/RSectionModule.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */

namespace cascade\components\section;

use infinite\base\collector\Item as BaseItem;

class Module extends \cascade\components\base\CollectorModule implements SectionInterface
{
    use SectionTrait;

    public $version = 1;
    public $priority = 1000; //lower is better

    public function getModuleType()
    {
        return 'Section';
    }

    public function getCollectorName()
    {
        return 'sections';
    }

    public function setTitle($value)
    {
        $this->_title = $value;
    }

    public function getCollectedObject(BaseItem $item)
    {
        $widget = $this->widget;
        $widget->collectorItem = $this->collectorItem = $item;
        $this->collectorItem->priority = $this->priority;
        $this->collectorItem->title = $this->_title;

        return $widget;
    }

}
