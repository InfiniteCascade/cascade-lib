<?php
namespace cascade\components\web\widgets\base;

use Yii;

use infinite\base\exceptions\Exception;
use infinite\helpers\ArrayHelper;
use infinite\helpers\Html;

use cascade\components\types\Relationship;

trait ObjectWidgetTrait
{
	protected $_dataProvider;

	public function generateStart() {
		$this->htmlOptions['data-instructions'] = json_encode($this->refreshInstructions);
		return parent::generateStart();
	}

	public function getRefreshInstructions() {
		$i = [];
		$i['type'] = 'widget';
		$i['systemId'] = $this->collectorItem->systemId;
		$i['recreateParams'] = $this->recreateParams;
		return $i;
	}

	public function getWidgetClasses() {
		$classes = parent::getWidgetClasses();
		$classes[] = 'refreshable';
		$queryModelClass = $this->owner->primaryModel;
		$classes[] = 'model-'. $queryModelClass::baseClassName();
		return $classes;
	}

	public function getDataProvider() {
		if (is_null($this->_dataProvider)) {
			$dataProvider = $this->dataProviderSettings;
			if (!isset($dataProvider['class'])) {
				$dataProvider['class'] = 'yii\data\ActiveDataProvider';
			}
			$method = ArrayHelper::getValue($this->settings, 'queryRole', 'all');
			if (in_array($method, ['parents', 'children']) && empty(Yii::$app->request->object)) {
				throw new Exception("Object widget requested when no object has been set!");
			}
			$queryModelClass = $this->owner->primaryModel;
			switch ($method) {
				case 'parents':
					$dataProvider['query'] = Yii::$app->request->object->relativesQuery('parents', $queryModelClass);
				break;
				case 'children':
					$dataProvider['query'] = Yii::$app->request->object->relativesQuery('children', $queryModelClass);
				break;
				default:
					$dataProvider['query'] = $queryModelClass::find();
				break;
			}
			
			$currentSortBy = $this->currentSortBy;
			$currentSortByDirection = $this->currentSortByDirection;

			

			$dataProvider['pagination'] = $this->paginationSettings;
			$this->_dataProvider = Yii::createObject($dataProvider);
		}
		return $this->_dataProvider;
	}

	public function getSortBy() {
		$sortBy = [];
		$dummyModel = $this->owner->dummyModel;
		$descriptorField = $dummyModel->descriptorField;
		if (is_array($descriptorField)) {
			$descriptorLabel = $dummyModel->getAttributeLabel('descriptor');
			$descriptorField = implode(',', array_reverse($descriptorField));
		} else {
			$descriptorLabel = $dummyModel->getAttributeLabel($descriptorField);
		}
		$sortBy['familiarity'] = [
			'label' => 'Familiarity'
		];
		$sortBy[$descriptorField] = [
			'label' => $descriptorLabel
		];
		return $sortBy;
	}

	public function getListItemOptions($model, $key, $index)
	{
		$options = self::getListItemOptionsBase($model, $key, $index);
		$relationModel = $this->getRelationModel($model);
		if ($relationModel && !empty($relationModel->primary)) {
			Html::addCssClass($options, 'active');
		}
		return $options;
	}

	public function getCurrentSortBy()
	{
		return $this->getState('sortBy', 'familiarity');
	}

	public function getCurrentSortByDirection()
	{
		return $this->getState('sortByDirection', ($this->currentSortBy === 'familiarity') ? 'desc' : 'asc');
	}

	public function getHeaderMenu()
	{
		$menu = [];

		$baseCreate = ['object/create'];
		$typePrefix = null;
		$method = ArrayHelper::getValue($this->settings, 'queryRole', 'all');
		$create = true;
		$link = false;

		if (($create || $link) && in_array($method, ['parents', 'children'])) {
			if (empty(Yii::$app->request->object) || empty($this->settings['relationship'])) {
				throw new Exception("Object widget requested when no object has been set!");
			}
			$create = $link = Yii::$app->gk->canGeneral('update', Yii::$app->request->object);
			$baseCreate['object_id'] = Yii::$app->request->object->primaryKey;
			if ($method === 'parents') {
				$typePrefix = 'parent:';
			} else {
				$typePrefix = 'child:';
			}
			if ($link) {
				$link = false;
				$objectModule = $this->owner;
				if ($objectModule && !$objectModule->uniparental) {
					$link = true;
				}
			}
		}
		$baseCreate['type'] = $typePrefix . $this->owner->systemId;

		if ($create && Yii::$app->gk->canGeneral('create', $this->owner->primaryModel)) {
			$createUrl = $baseCreate;
			$menu[] = [
				'label' => '<i class="fa fa-plus"></i>',
				'linkOptions' => ['title' => 'Create'],
				'url' => $createUrl
			];
		}
		if ($link) {
			$createUrl = $baseCreate;
			$createUrl['link'] = 1;
			$menu[] = [
				'label' => '<i class="fa fa-link"></i>',
				'linkOptions' => ['title' => 'Link'],
				'url' => $createUrl
			];
		}

		//sorting
		$sortBy = $this->sortBy;
		$currentSortBy = $this->currentSortBy;
		$currentSortByDirection = $this->currentSortByDirection;
		$oppositeSortByDirection = ($currentSortByDirection === 'asc') ? 'desc' : 'asc';

		if (!empty($sortBy)) {
			$item = [
				'label' => '<i class="fa fa-sort"></i>',
				'linkOptions' => ['title' => 'Sort by'],
				'url' => '#',
				'items' => [],
				'options' => ['class' => 'dropleft']
			];

			foreach ($sortBy as $sortKey => $sortItem) {
				$newSortByDirection = 'asc';
				$isActive = $sortKey === $currentSortBy;
				$extra = '';
				if ($isActive) {
					$extra = '<i class="pull-right fa fa-sort-'.$oppositeSortByDirection.'"></i>';
					$newSortByDirection = $oppositeSortByDirection;
				}

				$stateChange = [
					$this->stateKeyName('sortBy') => $sortKey, 
					$this->stateKeyName('sortByDirection') => $newSortByDirection
				];

				$item['items'][] = [
					'label' => $sortItem['label'] . $extra,
					'linkOptions' => [
						'title' => 'Sort by '. $sortItem['label'], 
						'data-state-change' => json_encode($stateChange)
					],
					'options' => [
						'class' => $isActive ? 'active' : ''
					],
					'url' => '#',
					'active' => $isActive
				];
			}
			$menu[] = $item;
		}

		return $menu;
	}

	public function getRelationModel($model) {
		$queryRole = ArrayHelper::getValue($this->settings, 'queryRole', false);
		$relationship = ArrayHelper::getValue($this->settings, 'relationship', false);
		if ($queryRole && $relationship) {
			if ($queryRole === 'children') {
				return $relationship->getModel(Yii::$app->request->object->primaryKey, $model->primaryKey);
			} else {
				return $relationship->getModel($model->primaryKey, Yii::$app->request->object->primaryKey);
			}
		}
		return false;
	}

	public function getMenuItems($model, $key, $index)
	{
		$objectType = $model->objectType;

		$menu = [];
		$baseUrl = ['id' => $model->primaryKey];
		$queryRole = ArrayHelper::getValue($this->settings, 'queryRole', false);
		$relationship = ArrayHelper::getValue($this->settings, 'relationship', false);
		$relationModel = $this->getRelationModel($model);

		if ($relationModel) {
			if ($queryRole === 'children') {
				$baseUrl['object_relation'] = 'child';
			} else {
				$baseUrl['object_relation'] = 'parent';
			}
			$baseUrl['relation_id'] = $relationModel->primaryKey;
			if ($relationModel->getBehavior('PrimaryRelation') !== null && $relationModel->presentSetPrimaryOption) {
				$menu['primary'] = [
					'icon' => 'fa fa-star',
					'label' => 'Set as primary',
					'url' => ['object/update', 'subaction' => 'setPrimary'] + $baseUrl,
					'linkOptions' => ['data-handler' => 'background']
				];
			}
		}

		// update button
		if (!$objectType->hasDashboard && $model->can('update')) {
			$menu['update'] = [
				'icon' => 'fa fa-wrench',
				'label' => 'Update',
				'url' => ['object/update'] + $baseUrl,
				'linkOptions' => ['data-handler' => 'background']
			];
		}

		
		// delete button
		if ($model->can('delete')) {
			$deleteUrl = ['object/delete', ];
			$menu['delete'] = [
				'icon' => 'fa fa-trash-o',
				'label' => 'Delete',
				'url' => ['object/delete'] + $baseUrl,
				'linkOptions' => ['data-handler' => 'background']
			];
		}

		return $menu;
	}

	protected function getPossibleMenuItems($model)
	{
		$possible = [];
		return $possible;
	}
	

	public function getPaginationSettings() {
		return ['class' => 'yii\data\Pagination', 'pageSize' => 20];
	}

	public function getPagerSettings() {
		return ['class' => 'yii\widgets\LinkPager'];
	}

	public function getDataProviderSettings() {
		return ['class' => 'yii\data\ActiveDataProvider'];
	}
}
?>