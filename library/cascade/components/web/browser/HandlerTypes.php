<?php
namespace cascade\components\web\browser;

use Yii;
use infinite\helpers\Html;
use infinite\base\exceptions\Exception;
use yii\base\InvalidConfigException;
use cascade\components\types\Relationship;
use infinite\helpers\ArrayHelper;


class HandlerTypes extends \infinite\web\browser\Handler
{
	public $bundleClass = 'cascade\\components\\web\\browser\\Bundle';
	public function getTotal()
	{
		return count($this->items);
	}

	public function getItems()
	{
		$instructions = $this->instructions;
		$items = [];
		if (isset($instructions['relationshipRole'])) {
			if (!isset($instructions['relationship'])) {
				throw new InvalidConfigException("Relationship type tasks require a relationship ID");
			}
			$relationship = Relationship::getById($instructions['relationship']);
			if (empty($relationship)) {
				throw new InvalidConfigException("Relationship type tasks require a relationship");
			}
			if ($instructions['relationshipRole'] === 'parent') {
				$relationships = $relationship->parent->collectorItem->parents;
			} else {
				$relationships = $relationship->child->collectorItem->parents;
			}
			$types = ArrayHelper::getColumn($relationships, 'parent');
		} else {
			$types = Yii::$app->collectors['types']->getAll();
		}

		foreach ($types as $type) {
			$items[] = [
				'type' => 'type',
				'id' => $type->systemId,
				'label' => $type->title->upperPlural,
				'hasChildren' => true
			];
		}
		return $items;
	}
}
?>