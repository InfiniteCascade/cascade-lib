<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\section;

use canis\base\collector\CollectedObjectInterface;
use canis\base\collector\CollectedObjectTrait;

/**
 * Item [[@doctodo class_description:cascade\components\section\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \canis\base\collector\Item implements SectionInterface, CollectedObjectInterface
{
    use SectionTrait;
    use CollectedObjectTrait;
}
