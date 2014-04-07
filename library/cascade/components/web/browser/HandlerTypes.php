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
		if (!isset($instructions['hasDashboard'])) {
			$instructions['hasDashboard'] = [true, false];
		} elseif (!is_array($instructions['hasDashboard'])) {
			$instructions['hasDashboard'] = [$instructions['hasDashboard']];
		}
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
			$types = ArrayHelper::map($relationships, 'parent.systemId', 'parent');
		} else {
			$typesRaw = Yii::$app->collectors['types']->getAll();
			$types = ArrayHelper::map($typesRaw, 'object.systemId', 'object');
		}
		unset($types['']);
		foreach ($types as $typeKey => $type) {
			if (isset($instructions['limitTypes'])) {
				if (!in_array($typeKey, $instructions['limitTypes'])) {
					continue;
				}
			}
			if (!in_array($type->hasDashboard, $instructions['hasDashboard'])) {
				continue;
			}
			$item = [
				'type' => 'type',
				'id' => $type->systemId,
				'label' => $type->title->upperPlural
			];
			if (!$this->filterQuery || preg_match('/'. preg_quote($this->filterQuery) .'/i', $item['label']) === 1) {
				$items[] = $item; 
			}
		}
		if (!$this->filterQuery) {
			ArrayHelper::multisort($items, 'label', SORT_ASC);
		} else {
			$filterQuery = $this->filterQuery;
			usort($items, function($a, $b) use ($filterQuery) {
				$a = levenshtein($a['label'], $filterQuery);
				$b = levenshtein($b['label'], $filterQuery);
				return ($a < $b) ? -1 : 1;
			});
		}
		return $items;
	}
}
?>