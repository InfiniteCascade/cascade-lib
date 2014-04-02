<?php
namespace cascade\components\web\browser;

use Yii;
use infinite\helpers\Html;
use infinite\base\exceptions\Exception;


class Response extends \infinite\web\browser\Response
{
	public $bundleClass = 'cascade\\components\\web\\browser\\Bundle';

	public function parseStack($request)
	{
		$instructions = [];
		if (!isset($request['stack'])) {
			return false;
		}
		\d($request);exit;
		return $instructions;
	}
}
?>