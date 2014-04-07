<?php
namespace cascade\components\web\browser;

use Yii;
use infinite\helpers\Html;
use infinite\base\exceptions\Exception;


class Response extends \infinite\web\browser\Response
{
	public $bundleClass = 'cascade\\components\\web\\browser\\Bundle';

	public static function defaultInstructions()
	{
		return [
			'offset' => 0
		];
	}
	public static function parseStack($request)
	{
		$instructions = [];
		if (empty($request['stack'])) {
			return false;
		}

		$lastItem = array_pop($request['stack']);
		if (!isset($lastItem['type'])) { return false; }
		$instructions['id'] = $request['id'];
		$registryClass = Yii::$app->classes['Registry'];
		switch ($lastItem['type']) {
			case 'type': //object type
				$parentItem = false;
				$instructions['handler'] = 'objects';
				if (!empty($request['stack'])) {
					$parentItem = array_pop($request['stack']);
				}
				$type = Yii::$app->collectors['types']->getOne($lastItem['id']);
				if (!$type) { return false; }
				$instructions['type'] = $lastItem['id'];
				if ($parentItem && $parentItem['type'] === 'object' && !empty($parentItem['id'])) {
					$instructions['parent'] = $parentItem['id'];
				}
			break;
			case 'object': //object type
				$object = $registryClass::getObject($lastItem['id']);
				if (!$object) { return false; }
				$objectTypeItem = $object->objectTypeItem;
				$objectType = $objectTypeItem->object;
				if (!isset($request['modules'])) {
					$request['modules'] = array_keys(Yii::$app->collectors['types']->getAll());
				}
				$possibleTypes = HandlerTypes::possibleTypes($objectType, $request['modules']);
				
				//\d(array_keys($possibleTypes));exit;
				if (empty($possibleTypes)) {
					return false;
				} elseif (count($possibleTypes) === 1) {
					$type = array_pop($possibleTypes);
					$instructions['handler'] = 'objects';
					$instructions['type'] = $type->systemId;
					$instructions['parent'] = $object->primaryKey;
				} else {
					$instructions['handler'] = 'types';
					$instructions['limitTypes'] = array_keys($possibleTypes);
				}
			break;
		}

		return $instructions;
	}
}
?>