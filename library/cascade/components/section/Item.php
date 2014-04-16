<?php

namespace cascade\components\section;

use infinite\base\collector\CollectedObjectInterface;
use infinite\base\collector\CollectedObjectTrait;

class Item extends \infinite\base\collector\Item implements SectionInterface, CollectedObjectInterface
{
    use SectionTrait;
    use CollectedObjectTrait;
    protected $_priority = 0;

    public function getPriority()
    {
        return $this->_priority;
    }

    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }
}
