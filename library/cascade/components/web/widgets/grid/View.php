<?php

namespace cascade\components\web\widgets\grid;

use Yii;

use cascade\web\widgets\grid\columns\Data as DataColumn;

use infinite\base\exceptions\Exception;

class View extends \yii\base\Widget {
	public $widget;
	public $state;
	public $dataProvider;
	public $emptyText = 'No items found';
	public $htmlOptions = array();
	public $sortableAttributes;
	public $filters;
	public $views = array('list');
	public $currentView = 'list';
	public $itemsPerRequest = 20;
	public $limit;
	public $rendererSettings = array();
	public $itemMenu = array();
	public $additionalClasses;
	public $specialItemClasses = array();

	public $nullDisplay = '';

	protected $_totalItems;
	protected $_currentData;
	protected $_currentDataRaw;
	protected $_columns;
	protected $_columnSettings;
	protected $_formatter;

	public function init() {
		if($this->dataProvider===null) {
			throw new Exception(Yii::t('zii','The "dataProvider" property cannot be empty.'));
		}

		$this->htmlOptions['id']=$this->getId();
		$this->htmlOptions['class']='grid-view';
		$this->_prepareDataProvider();
	}

	public function run() {
		$this->_prepareDataProvider();
		$data = $this->getData();
		if (!empty($this->state['fetch'])) {
			ob_clean();
			// you need a subset of the data
			Yii::$app->controller->json($data);
			Yii::$app->end();
		} else {
			$columnSettings = $this->getColumnSettings();
			$options = array();
			$options['currentPage'] = $this->dataProvider->pagination->currentPage + 1;
			$options['widget'] = $this->widget;
			$options['state'] = $this->state;
			$options['columns'] = $columnSettings;
			$options['data'] = $data;
			$options['totalItems'] = $this->getTotalItems();
			$options['currentView'] = $this->currentView;
			$options['views'] = $this->views;
			$options['itemMenu'] = $this->itemMenu;
			$options['loadMore'] = is_null($this->limit);
			$options['emptyText'] = $this->emptyText;
			$options['specialItemClasses'] = $this->specialItemClasses;
			$options['rendererSettings'] = $this->rendererSettings;
			$options['rendererSettings']['grid']['sortableLabel'] = 'Sort by:';
			$options['rendererSettings']['grid']['sortable'] = $this->sortableAttributes;
			$options=CJSON::encode($options);
			if (!empty($this->additionalClasses)) {
				$this->htmlOptions['class'] .= ' '. $this->additionalClasses;
			}
			$this->htmlOptions['data-grid-view-options'] = $options;
			echo Html::tag('div', '', $this->htmlOptions);
		}
	}
	
	public function getColumnSettings() {
		if (is_null($this->_columnSettings)) {
			$this->_columnSettings = array();
			foreach ($this->columns as $key => $c) {
				if (!$c->visible) { continue; }
				$this->_columnSettings[$key] = array('label' => $c->getDataLabel());

				if (!isset($c->htmlOptions)) {
					$c->htmlOptions = array();
				}
				$this->_columnSettings[$key]['htmlOptions'] = $c->htmlOptions;
				$sortableResolve = $this->dataProvider->sort->resolveAttribute($c->name);
				$this->_columnSettings[$key]['sortable'] = !empty($sortableResolve);
			}
		}
		return $this->_columnSettings;
	}

	public function getData() {
		if (is_null($this->_currentData)) {
			$this->_currentDataRaw = $this->dataProvider->getData();
			$this->_currentData = array();
			$itemNumber = $this->dataProvider->pagination->offset;
			$row = 0;
			foreach ($this->_currentDataRaw as $r) {
				$p = array('itemNumber' => $itemNumber, 'id' => $r->primaryKey, 'values' => array());
				foreach ($this->columns as $key => $c) {
					$p['values'][$key] = $c->getDataValue($row, $r, false);
				}
				$p['acl'] = array();
				if ($this->owner->instanceSettings['whoAmI'] === 'parent' AND isset($r->childObject) AND $r->childObject->hasBehavior('Access')) {
					$p['acl'] = $r->childObject->aclSummary();
				} elseif($this->owner->instanceSettings['whoAmI'] === 'child' AND isset($r->parentObject) AND $r->parentObject->hasBehavior('Access')) {
					$p['acl'] = $r->parentObject->aclSummary();
				} elseif ($r->hasBehavior('Access')) {
					$p['acl'] = $r->aclSummary();
				}
				$this->_currentData['item-'. $itemNumber] = $p;
				$row++; $itemNumber++;
			}
		}
		return $this->_currentData;
	}

	public function setColumns($columns) {
		$this->_columns = array();
		foreach ($columns as $key => $columnName) {
			if (is_array($columnName)) {
				$settings = $columnName;
				$settings['name'] = $key;
			} else {
				$settings = array('name' => $columnName);
			}
			if (!isset($settings['class'])) {
				$settings['class'] = '\cascade\web\widgets\grid\columns\Data';
			}
			if (!isset($settings['value'])) {
				$settings['type'] = 'raw';
				$settings['value'] = function($data, $row) use ($settings) {
					$key = explode('.', $settings['name']);
					$object = $data;
					while (count($key) > 1) {
						$next = array_shift($key);
						if (is_object($object)) {
							$object = $object->{$next};
						} else {
							$object = $object[$next];
						}
					}
					$key = $key[0];
					if (is_object($object)) {
						$model = get_class($object);
						$fields = $model::getFields($object);
						if (isset($fields[$key])) {
							return $fields[$key]->getFormattedValue();
						}
					}
					return $object->{$key};
				};
			}
			if (!isset($settings['type'])) {
				$settings['type'] = 'raw';
			}
			$column = Yii::createObject($settings, $this);
			$key = $column->name;

			if (!$column->visible) {
			//	continue;
			}
			$this->_columns[$key] = $column;
		}
	}

	protected function createGridColumn($text) {
		if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
			throw new Exception(Yii::t('zii', 'The column must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));
		}
		$column=new DataColumn($this);
		$column->name=$matches[1];
		if (isset($matches[3]) && $matches[3]!=='')
			$column->type=$matches[3];
		if (isset($matches[5]))
			$column->header=$matches[5];
		return $column;
	}

	public function getColumns() {
		if (is_null($this->_columns)) {
			$this->columns = $this->dataProvider->model->attributeNames();
		}
		return $this->_columns;
	}

	public function getDataKey() {
		return 'ajax-'. $this->id;
	}

	public function getTotalItems() {
		if (is_null($this->_totalItems)) {
			$this->_totalItems = $this->dataProvider->totalItemCount;
		}
		return $this->_totalItems;
	}

	protected function _prepareDataProvider() {
		if (!is_null($this->limit)) {
			$this->dataProvider->pagination->pageSize = $this->limit;
		} else {
			$this->dataProvider->pagination->pageSize = $this->itemsPerRequest;
		}
	}
	
	/**
	 *
	 *
	 * @return CFormatter the formatter instance. Defaults to the 'format' application component.
	 */
	public function getFormatter() {
		if ($this->_formatter===null)
			$this->_formatter=Yii::$app->format;
		return $this->_formatter;
	}


	/**
	 *
	 *
	 * @param CFormatter $value the formatter instance
	 */
	public function setFormatter($value) {
		$this->_formatter=$value;
	}
}
?>