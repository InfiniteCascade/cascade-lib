<?php
namespace cascade\components\storageHandlers;

use infinite\base\collector\CollectedObjectTrait;
use infinite\helpers\Html;

abstract class Handler extends \infinite\base\Component implements \infinite\base\collector\CollectedObjectInterface {
	use CollectedObjectTrait;


	abstract public function generateInternal($item);
	abstract public function validate($model, $attribute);

	public function generate($item)
	{
		$rendered = $this->generateInternal($item);
		if ($rendered) {
			$this->prepareRendered($rendered, $item);
		}
		return $rendered;
	}

	public function prepareRendered(&$rendered, $item)
	{
	}

}
?>