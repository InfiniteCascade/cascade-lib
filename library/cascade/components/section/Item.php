<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\section;

use teal\base\collector\CollectedObjectInterface;
use teal\base\collector\CollectedObjectTrait;

/**
 * Item [[@doctodo class_description:cascade\components\section\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \teal\base\collector\Item implements SectionInterface, CollectedObjectInterface
{
    use SectionTrait;
    use CollectedObjectTrait;
}
