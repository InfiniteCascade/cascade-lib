<?php

namespace cascade\components\helpers;

use Yii;

class StringHelper extends \infinite\helpers\StringHelper {
	static public function parseInstructions()
	{
		$instructions = parent::parseInstructions();
		$instructions['type'] = function($instructions) {
			if (count($instructions) >= 2) {
				$placementType = array_shift($instructions);
				$placementItem = Yii::$app->collectors['types']->getOne($placementType);
				if (isset($placementItem)) {
					$placementItem = $placementItem->object;
				}
				while (!empty($placementItem) AND is_object($placementItem) AND !empty($instructions)) {
					$nextInstruction = array_shift($instructions);
					if (isset($placementItem->{$nextInstruction})) {
						$placementItem = $placementItem->{$nextInstruction};
					} else {
						$placementItem = null;
					}
				}
				if (is_null($placementItem)) { return null; }
				return (string)$placementItem;
			}
		};
		return $instructions;
	}
}