<?php

namespace cascade\components\section;

use infinite\base\collector\CollectedObjectInterface;
use infinite\base\collector\CollectedObjectTrait;

class Item extends \infinite\base\collector\Item implements SectionInterface, CollectedObjectInterface {
	use SectionTrait;
	use CollectedObjectTrait;
	public $displayPriority = 0;
}


?>
