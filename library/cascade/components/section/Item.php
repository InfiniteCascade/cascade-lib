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
 */
class Item extends \infinite\base\collector\Item implements SectionInterface, CollectedObjectInterface
{
    use SectionTrait;
    use CollectedObjectTrait;
}
