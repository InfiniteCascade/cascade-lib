<?php
namespace cascade\components\web\browser;

use Yii;
use infinite\helpers\Html;
use infinite\base\exceptions\Exception;
use yii\base\InvalidConfigException;
use cascade\components\types\Relationship;
use infinite\helpers\ArrayHelper;


class HandlerObjects extends \infinite\web\browser\Handler
{
	public $bundleClass = 'cascade\\components\\web\\browser\\Bundle';
	protected $_dataSource;

	public function getDataSource()
	{
		if (is_null($this->_dataSource)) {
			$typeItem = Yii::$app->collectors['types']->getOne($this->instructions['type']);
			if (!$typeItem || !($type = $typeItem->object)) { return $this->_dataSource = false; }
			$primaryModel = $type->primaryModel;
			if (isset($this->instructions['parent'])) {
				$registryClass = Yii::$app->classes['Registry'];
				$object = $registryClass::getObject($this->instructions['parent']);
				if (!$object) { return $this->_dataSource = false; }
				$this->_dataSource = $object->queryChildObjects($primaryModel, [], []);
			} else {
				$this->_dataSource = $primaryModel::find();
			}
			$dummyModel = new $primaryModel;
			$sortOptions = array_values($dummyModel->sortOptions);
			if ($this->filterQuery) {
				$primaryModel::simpleSearchTermQuery($this->_dataSource, $this->filterQuery);
			} elseif (isset($sortOptions[0])) {
				$this->_dataSource->orderBy($sortOptions[0]);
			}
		}
		return $this->_dataSource;
	}

	public function getTotal()
	{
		if (!$this->dataSource) {
			return false;
		}
		return $this->dataSource->count();
	}

	public function getItems()
	{
		$instructions = $this->instructions;
		
		if (!$this->dataSource) {
			return false;
		}
		$dataSource = clone $this->dataSource;
		if (!$this->filterQuery) {
			$dataSource->limit($this->bundle->limit);
			$dataSource->offset($this->bundle->offset);
		}
		$items = [];
		foreach ($dataSource->all() as $object) {
			$items[] = [
				'type' => 'object',
				'id' => $object->primaryKey,
				'label' => $object->descriptor
			];
		}
		return $items;
	}
}
?>