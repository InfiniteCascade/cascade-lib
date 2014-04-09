<?php
namespace cascade\components\web\browser;

use Yii;
use infinite\base\exceptions\Exception;
use yii\base\InvalidConfigException;

class Item extends \infinite\web\browser\Item
{
	public $objectType = false;

	public function package()
	{
		return parent::package() + [
			'objectType' => $this->objectType
		];
	}
}
?>