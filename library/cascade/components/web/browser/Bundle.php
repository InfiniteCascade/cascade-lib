<?php
namespace cascade\components\web\browser;

use Yii;
use infinite\base\exceptions\Exception;
use yii\base\InvalidConfigException;

class Bundle extends \infinite\web\browser\Bundle
{
	public function predictTotal()
	{
		return false;
	}

	public function getHandlers()
	{
		return [
			'types' => 'cascade\\components\\web\\browser\\HandlerTypes'
		];
	}
}
?>