<?php
namespace cascade\components\web\themes;

use infinite\helpers\ArrayHelper;

class Exception extends \infinite\base\exceptions\Exception
{
	public function getName()
	{
		return 'Theme';
	}
}
?>