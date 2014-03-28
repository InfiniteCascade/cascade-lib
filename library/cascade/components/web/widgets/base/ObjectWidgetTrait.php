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
	public $pageSize = 5;
	
	public function generateStart() {
		$this->htmlOptions['data-instructions'] = json_encode($this->refreshInstructions);
		return parent::generateStart();
	}

	public function getRefreshInstructions() {
		$i = [];
		$i['type'] = 'widget';
		$i['systemId'] = $this->collectorItem->systemId;
		$i['recreateParams'] = $this->recreateParams;
		if ($this->section) {
			$i['section'] = $this->section->systemId;
		}
		return $i;
	}

	public function getWidgetClasses() {
		$classes = parent::getWidgetClasses();
		$classes[] = 'refreshable';
		$queryModelClass = $this->owner->primaryModel;
		$classes[] = 'model-'. $queryModelClass::baseClassName();
		return $classes;
	}

	public function getDataProvider()
	{
		if (is_null($this->_dataProvider)) {
			$dataProvider = $this->dataProviderSettings;
			if (!isset($dataProvider['class'])) {
				$dataProvider['class'] = 'infinite\\data\\ActiveDataProvider';
			}
			$method = ArrayHelper::getValue($this->settings, 'queryRole', 'all');
			if (in_array($method, ['parents', 'children']) && empty(Yii::$app->request->object)) {
				throw new Exception("Object widget requested when no object has been set!");
			}
			$queryModelClass = $this->owner->primaryModel;
			switch ($method) {
				case 'parents':
					$dataProvider['query'] = Yii::$app->request->object->queryParentObjects($queryModelClass);
				break;
				case 'children':
					$dataProvider['query'] = Yii::$app->request->object->queryChildObjects($queryModelClass);
				break;
				default:
					$dataProvider['query'] = $queryModelClass::find();
				break;
			}
			$dummyModel = new $queryModelClass;
			if (in_array($this->currentSortBy, ['familiarity', 'last_accessed'])) {
				if ($dataProvider['query']->getBehavior('FamiliarityQuery') === null) {
					$dataProvider['query']->attachBehavior('FamiliarityQuery', ['class' => 'cascade\\components\\db\\behaviors\\QueryFamiliarity']);
				}
				$dataProvider['query']->withFamiliarity();
			}
			$dataProvider['pagination'] = $this->paginationSettings;
			$dataProvider['sort'] = $this->sortSettings;
			$this->_dataProvider = Yii::createObject($dataProvider);
		}
		return $this->_dataProvider;
	}

	public function getSortBy()
	{
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
		$sortBy['last_accessed'] = [
			'label' => 'Last Accessed'
		];
		$sortBy[$descriptorField] = [
			'label' => $descriptorLabel
		];
		return $sortBy;
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

		$baseCreate = ['/object/create'];
		$typePrefix = null;
		$method = ArrayHelper::getValue($this->settings, 'queryRole', 'all');
		$relationship = ArrayHelper::getValue($this->settings, 'relationship', false);
		$create = true;
		$link = false;

		if (($create || $link) && in_array($method, ['parents', 'children'])) {
			if (empty(Yii::$app->request->object) || !$relationship) {
				throw new Exception("Object widget requested when no object has been set!");
			}
			$create = $link = Yii::$app->gk->canGeneral('update', Yii::$app->request->object);
			$baseCreate['object_id'] = Yii::$app->request->object->primaryKey;
			$objectRole = $relationship->companionRole($method);
			$companionRole = $relationship->companionRole($objectRole);
			$typePrefix = $companionRole .':';
			$link = $link && $relationship->canLink($objectRole, Yii::$app->request->object);
			$create = $create && $relationship->canCreate($objectRole, Yii::$app->request->object);
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
					'label' => $extra . $sortItem['label'],
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

	public function getListItemOptions($model, $key, $index)
	{
		$options = self::getListItemOptionsBase($model, $key, $index);
		//return $options;
		$objectType = $model->objectType;

		$queryRole = ArrayHelper::getValue($this->settings, 'queryRole', false);
		$relationship = ArrayHelper::getValue($this->settings, 'relationship', false);

		if (!$relationship) {
			return $options;
		}
		
		if ($queryRole === 'children') {
			$baseUrl['object_relation'] = 'child';
			$primaryRelation = $relationship->getPrimaryChild(Yii::$app->request->object);
			$key = 'child_object_id';
		} else {
			$baseUrl['object_relation'] = 'parent';
			$primaryRelation = $relationship->getPrimaryParent($model);
			$key = 'parent_object_id';
		}
		if ($primaryRelation && $primaryRelation->{$key} === $model->primaryKey) {
			Html::addCssClass($options, 'active');
		}

		return $options;
	}

	public function getMenuItems($model, $key, $index)
	{
		$objectType = $model->objectType;

		$menu = [];
		$baseUrl = ['id' => $model->primaryKey];
		$queryRole = ArrayHelper::getValue($this->settings, 'queryRole', false);
		$relationship = ArrayHelper::getValue($this->settings, 'relationship', false);
		// $relationModel = $this->getRelationModel($model);
		$baseUrl['related_object_id'] = Yii::$app->request->object->primaryKey;
		$baseUrl['relationship_id'] = $relationship->systemId;

		if ($queryRole === 'children') {
			$baseUrl['object_relation'] = 'child';
			$primaryRelation = $relationship->getPrimaryChild(Yii::$app->request->object);
		} else {
			$baseUrl['object_relation'] = 'parent';
			$primaryRelation = $relationship->getPrimaryParent($model);
		}
		if ($primaryRelation && $primaryRelation->primaryKey !== $model->primaryKey) {
			$relationUrl = ['id' => Yii::$app->request->object->primaryKey];
			$relationUrl['child_object_id'] = $model->primaryKey;
			$menu['primary'] = [
				'icon' => 'fa fa-star',
				'label' => 'Set as primary',
				'url' => ['/object/update', 'subaction' => 'setPrimary'] + $relationUrl,
				'linkOptions' => ['data-handler' => 'background']
			];
		}

		// update button
		if (!$objectType->hasDashboard && $model->can('update')) {
			$menu['update'] = [
				'icon' => 'fa fa-wrench',
				'label' => 'Update',
				'url' => ['/object/update'] + $baseUrl,
				'linkOptions' => ['data-handler' => 'background']
			];
		}

		if (!$objectType->hasDashboard && $model->can('manageAccess')) {
			$menu['access'] = [
				'icon' => 'fa fa-key',
				'label' => 'Access',
				'url' => ['/object/access'] + $baseUrl,
				'linkOptions' => ['data-handler' => 'background']
			];
		}
		
		// delete button
		if ($model->can('delete')) {
			$menu['delete'] = [
				'icon' => 'fa fa-trash-o',
				'label' => 'Delete',
				'url' => ['/object/delete'] + $baseUrl,
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

	public function getVariables()
	{
		$vars = [];
		if (
			isset($this->settings['relationship']) 
			&& isset($this->settings['queryRole'])
			&& $this->settings['relationship']->child === $this->settings['relationship']->parent
			) {

			if ($this->settings['queryRole'] === 'parents') {
				$vars['relationship'] = 'Parent';
			} elseif ($this->settings['queryRole'] === 'children') {
				$vars['relationship'] = 'Child';
			}
		}
		return array_merge(parent::getVariables(), $vars);
	}

	public function getPaginationSettings() {
		return [
			'class' => 'infinite\\data\\Pagination',
			'pageSize' => $this->pageSize,
			'validatePage' => false,
			'page' => $this->getState('_page', 0),
		];
	}

	public function getSortSettings() {
		return [
			'class' => 'infinite\\data\\Sort',
			'sortOrders' => [
				$this->currentSortBy => $this->currentSortByDirection === 'asc' ? SORT_ASC : SORT_DESC
			],
			'attributes' => $this->getSortBy()
		];
	}

	public function getPagerSettings() {
		return [
			'class' => 'infinite\\widgets\\LinkPager',
			'pageStateKey' => $this->stateKeyName('_page'),
		];
	}

	public function getDataProviderSettings() {
		return [
			'class' => 'infinite\\data\\ActiveDataProvider'
		];
	}
}
?>