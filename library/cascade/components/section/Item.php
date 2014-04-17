<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\section;

use infinite\base\collector\CollectedObjectInterface;
use infinite\base\collector\CollectedObjectTrait;

/**
 * Item [@doctodo write class description for Item]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Item extends \infinite\base\collector\Item implements SectionInterface, CollectedObjectInterface
{
    use SectionTrait;
    use CollectedObjectTrait;
    /**
     * @var __var__priority_type__ __var__priority_description__
     */
    protected $_priority = 0;

    /**
     * __method_getPriority_description__
     * @return __return_getPriority_type__ __return_getPriority_description__
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     * __method_setPriority_description__
     * @param __param_priority_type__ $priority __param_priority_description__
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }
}
