<?php

namespace cascade\controllers;

use Yii;

use cascade\models\Registry;
use cascade\models\Relation;
use cascade\models\ObjectFamiliarity;
use cascade\models\DeleteForm;
use cascade\components\types\Module as TypeModule;
use cascade\components\types\Relationship;
use cascade\components\web\ObjectViewEvent;

use infinite\helpers\ArrayHelper;
use infinite\web\Controller;
use infinite\db\ActiveRecord;
use infinite\base\exceptions\HttpException;

use yii\web\AccessControl;
use yii\web\VerbFilter;


class ObjectController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'allow' => false,
						'roles' => ['?'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'index' => ['get'],
					'view' => ['get'],
					'create' => ['get', 'post'],
					'setPrimary' => ['get'],
					'link' => ['get', 'post'],
					'setPrimary' => ['get'],
					'update' => ['get', 'post'],
					'delete' => ['get', 'post'],
					'watch' => ['get'],
					'unwatch' => ['get'],
					'widget' => ['get', 'post'],
					'unwatch' => ['get'],
					'search' => ['get'],
					'browse' => ['get'],
				],
			],
		];
	}

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\\web\\ErrorAction',
			]
		];
	}

	public function actionBrowse() {
		return $this->render('index');
	}


	/**
	 *
	 */
	public function actionSearch() {
		$package = [];
		$defaultParams = ['typeFilters' => []];
		$searchParams = array_merge($defaultParams, $_GET);

		if (empty($searchParams['term'])) {
			Yii::$app->response->data = $package;
			return;
		}

		$modules = isset($searchParams['modules']) ? (array)$searchParams['modules'] : array_keys(Yii::$app->collectors['types']->getAll());
		$params = ['ignore' => [], 'ignoreChildren' => [], 'ignoreParents' => []];
		if (!empty($searchParams['ignore'])) {
			$params['ignore'] = (array)$searchParams['ignore'];
		}
		if (!empty($searchParams['ignoreChildren'])) {
			$params['ignoreChildren'] = (array)$searchParams['ignoreChildren'];
		}
		if (!empty($searchParams['ignoreParents'])) {
			$params['ignoreParents'] = (array)$searchParams['ignoreParents'];
		}
		$params['modules'] = $modules;
		$term = $searchParams['term'];
		$scores = [];
		foreach ($modules as $module) {
			$moduleItem = Yii::$app->collectors['types']->getOne($module);
			if (!$moduleItem || !($moduleObject = $moduleItem->object)) { continue; }
			if (empty($moduleItem->object->searchWeight)) { continue; }
			if (in_array('authority', $searchParams['typeFilters']) && $moduleItem->object->getBehavior('Authority') === null) { continue; }
			if (in_array('dashboard', $searchParams['typeFilters']) && !$moduleItem->object->hasDashboard) { continue; }
			$moduleResults = $moduleObject->search($term, $params);
			foreach ($moduleResults as $r) {
				$package[] = $r->toArray();
			}
		}
		ArrayHelper::multisort($package, 'score', SORT_DESC);
		Yii::$app->response->data = $package;
	}


	/**
	 *
	 */
	public function actionView() {
		if (empty($_GET['id']) or !($object = $this->params['object'] = Registry::getObject($_GET['id'], false)) or !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
			throw new HttpException(404, "Unknown object.");
		}
		if (!$object->can('read')) {
			throw new HttpException(403, "Unable to access object.");
		}
		$action = isset($_GET['subaction']) ? $_GET['subaction'] : 'view';
		Yii::$app->request->object = $object;
		$type = $this->params['type'] = $object->objectType;
		$viewEvent = new ObjectViewEvent(['object' => $object, 'action' => $action]);
		$type->trigger(TypeModule::EVENT_VIEW_OBJECT, $viewEvent);

		if ($viewEvent->handled) {
			if ($viewEvent->accessed) {
				ObjectFamiliarity::accessed($object);
			}
			return;
		}
		
		if (!$type->hasDashboard) {
			throw new HttpException(400, "Bad request");
		}

		ObjectFamiliarity::accessed($object);
		Yii::$app->response->view = 'view';

		$sections = $this->params['sections'] = $typeItem->getSections($object);
		$this->params['active'] = $this->params['default'] = null;
		foreach ($sections as $section) {
			if ($section->priority > 0) {
				$this->params['active'] = $this->params['default'] = $section->systemId;
				break;
			}
		}
		if (!empty($_GET['section'])) {
			$this->params['active'] = $_GET['section'];
		}
	}



	protected function _parseParams($required = [], $can = null, $swap = false)
	{
		if (!empty($_GET['id']) && (!($this->params['object'] = Registry::getObject($_GET['id'], false)) || !($this->params['typeItem'] = $this->params['object']->objectTypeItem))) {
			throw new HttpException(404, "Unknown object.");
		}

		if (isset($this->params['object']) && (!($this->params['typeItem'] = $this->params['object']->objectTypeItem) || !($this->params['type'] = $this->params['typeItem']->object))) {
			throw new HttpException(404, "Unknown object type.");
		}
		
		if (isset($this->params['object'])) {
			$this->params['activeObject'] = $this->params['object'];
		}

		if (isset($this->params['type'])) {
			$this->params['activeType'] = $this->params['type'];
		}

		if (isset($this->params['typeItem'])) {
			$this->params['activeTypeItem'] = $this->params['typeItem'];
		}

		if (isset($_GET['related_object_id']) && isset($_GET['object_relation']) && isset($_GET['relationship_id'])) {
			$this->params['relationship'] = Relationship::getById($_GET['relationship_id']);
			if (!$this->params['relationship']) {
				throw new HttpException(404, "Unknown relationship type.");
			}
			$this->params['relatedModel'] = Registry::getObject($_GET['related_object_id'], true);
			if (isset($this->params['relatedModel']) && (!($this->params['relatedTypeItem'] = $this->params['relatedModel']->objectTypeItem) || !($this->params['relatedType'] = $this->params['relatedTypeItem']->object))) {
				throw new HttpException(404, "Unknown related object type.");
			}

			if ($_GET['object_relation'] === 'child') {
				$this->params['relation'] = $this->params['relationship']->getModel($_GET['related_object_id'], $this->params['object']->primaryKey);
			} else {
				$this->params['relation'] = $this->params['relationship']->getModel($this->params['object']->primaryKey, $_GET['related_object_id']);
			}
			if (empty($this->params['relation'])) {
				throw new HttpException(404, "Unknown relationship.");
			}

		}

		if (!$this->params['type']->hasDashboard || $this->params['type']->uniparental) {
			$required[] = 'relation';
		}

		if (!empty($_GET['link']) && !($this->params['relation'] = Relation::get($_GET['relation_id']))) {
			throw new HttpException(404, "Unknown relationship.");
		}

		if (!empty($_GET['relation_id']) && !($this->params['relation'] = Relation::get($_GET['relation_id']))) {
			throw new HttpException(404, "Unknown relationship.");
		}

		if (isset($this->params['relation']) && isset($this->params['object'])) {
			if (!isset($_GET['object_relation']) || !in_array($_GET['object_relation'], ['child', 'parent'])) {
				throw new HttpException(400, "Invalid request object relation");
			}
			$this->params['relatedObject'] = $this->params['object'];
			if (!is_null($can) && !$this->params['relatedObject']->can($can)) {
				throw new HttpException(403, "You are not authorized to perform this action.");
			}
			$can = 'update';
			$this->params['object'] = null;
			if ($_GET['object_relation'] === 'child') {
				$this->params['modelBucket'] = 'children';
				$this->params['object'] = Registry::getObject($this->params['relation']->parent_object_id);
			} else {
				$this->params['modelBucket'] = 'parents';
				$this->params['object'] = Registry::getObject($this->params['relation']->child_object_id);
			}
			if (!$this->params['object']) {
				throw new HttpException(404, "Unknown object.");
			}
			$this->params['object']->tabularId = ActiveRecord::getPrimaryTabularId();
			$this->params['object']->_moduleHandler = ActiveRecord::FORM_PRIMARY_MODEL;

			$this->params['subform'] = $_GET['object_relation'] . ':'. $this->params['relatedObject']->objectType->systemId;
			$this->params['relatedObject']->tabularId = $this->params['relatedObject']->id;
			$this->params['relation']->tabularId = $this->params['relatedObject']->id;
			$this->params['relatedObject']->_moduleHandler = $this->params['subform'];
			$this->params['relatedObject']->registerRelationModel($this->params['relation']);

		}

		if (!isset($this->params['handler']) && isset($this->params['object'])) {
			if (isset($this->params['relatedType'])) {
				$this->params['handler'] = $this->params['relatedType'];
			} else {
				$this->params['handler'] = $this->params['type'];
			}
		}

		if (!is_null($can) && isset($this->params['object']) && !$this->params['object']->can($can)) {
			throw new HttpException(403, "You are not authorized to perform this action.");
		}

		if (isset($_GET['subaction'])) {
			$this->params['subaction'] = $_GET['subaction'];
		}

		foreach ($required as $r) {
			if (!isset($this->params[$r])) {
				throw new HttpException(400, "Invalid request");
			}
		}
		if ($swap) {
			Yii::$app->request->object = $this->params['activeObject'];
		} else {
			Yii::$app->request->object = $this->params['object'];
		}
	}
	/**
	 *
	 */
	public function actionCreate() {
		if (!isset($_GET['type'])) { $_GET['type'] = ''; }
		$typeParsed = $originalTypeParsed= $_GET['type'];
		$subform = $object = null;
		$action = 'create';
		$saveSettings = [];

		$linkExisting = !empty($_GET['link']);
		if ($linkExisting) {
			$action = 'link';
		}

		if (!empty($_GET['object_id']) && (!($object = $this->params['object'] = Registry::getObject($_GET['object_id'], false)) || !($typeItem = $this->params['typeItem'] = $object->objectTypeItem))) {
			throw new HttpException(404, "Unknown object.");
		} elseif(isset($object)) {
			if (!$object->can('update')) {
				throw new HttpException(403, "Unable to update object.");
			}
			$typeParsedParts = explode(':', $typeParsed);
			if (count($typeParsedParts) >= 2 && in_array($typeParsedParts[0], ['parent', 'child'])) {
				$relationshipObjectField = $typeParsedParts[0] . '_object_id';
				$typeParsed = $typeParsedParts[1];
			} else {
				throw new HttpException(403, "Invalid request ");
			}
			$subform = implode(':', $typeParsedParts);
			$subformRelation = $originalTypeParsed;
			$saveSettings['allowEmpty'] = true;
		}

		if (empty($typeParsed) || !($type = Yii::$app->collectors['types']->getOne($typeParsed)) || !isset($type->object)) {
			throw new HttpException(404, "Unknown object type ". $typeParsed);
		}
		$module = $type->object;
		if (!Yii::$app->gk->canGeneral('create', $module->primaryModel)) {
			throw new HttpException(403, "You do not have access to create {$module->title->getPlural(true)}");
		}

		Yii::$app->response->view = 'create';
		Yii::$app->response->task = 'dialog';
		Yii::$app->response->taskOptions = ['title' => ucfirst($action) . ' '.$module->title->getSingular(true) , 'width' => '800px'];

		if (isset($object)) {
			$module = $object->objectType;
		}

		$models = false;
		if (!empty($_POST)) {
			list($error, $notice, $models, $niceModels) = $module->handleSaveAll(null, $saveSettings);
			if ($error) {
				Yii::$app->response->error = $error;
			} else {
				Yii::$app->response->task = 'status';
				$noticeExtra = '';
				if (!empty($notice)) {
					$noticeExtra = ' However, there were notices: '. $notice;
				}
				Yii::$app->response->success = '<em>'. $niceModels['primary']['model']->descriptor .'</em> was saved successfully.'.$noticeExtra;
				if (isset($subform)) {
					$primaryModel = $type->object->primaryModel;
					Yii::$app->response->trigger = [
						['refresh', '.model-'. $primaryModel::baseClassName()]
					];
				} else {
					Yii::$app->response->redirect = $niceModels['primary']['model']->getUrl('view');
				}
			}
		}
		if ($models === false) {
			$models = $module->getModels($object);
		}
		if (!($this->params['form'] = $module->getForm($models, ['subform' => $subform, 'linkExisting' => $linkExisting]))) {
			throw new HttpException(403, "There is nothing to {$action} for {$module->title->getPlural(true)}");
		}
		$this->params['form']->ajax = true;
	}

	/**
	 *
	 */
	public function actionUpdate() {
		$subform = null;
		$this->_parseParams(['object', 'type'], 'update');
		extract($this->params);
		$primaryModel = $type->primaryModel;
		if (isset($subaction) && $subaction === 'setPrimary') {
			if (!isset($relation)) {
				throw new HttpException(404, "Invalid relationship!");
			}
			Yii::$app->response->task = 'status';
			if ($relation->setPrimary()) {
				Yii::$app->response->trigger = [
					['refresh', '.model-'. $primaryModel::baseClassName()]
				];
			} else {
				Yii::$app->response->error = 'Unable to set primary object!';
			}
			return;
		} else {
			Yii::$app->response->view = 'create';
			Yii::$app->response->task = 'dialog';
			Yii::$app->response->taskOptions = ['title' => 'Update '. $type->title->getSingular(true)];
			$base = [];
			if (isset($relatedObject)) {
				$base[$relatedObject->tabularId] = $relatedObject;
				$base['relations'] = [$relatedObject->tabularId => $relation];
			}
			$models = $originalModels = $type->getModels($object, $base);

			if (!empty($_POST)) {
				list($error, $notice, $models, $niceModels) = $handler->handleSaveAll(null, ['allowEmpty' => true]);
				if ($error) {
					Yii::$app->response->error = $error;
				} else {
					$noticeExtra = '';
					if (!empty($notice)) {
						$noticeExtra = ' However, there were notices: '. $notice;
					}
					Yii::$app->response->success = '<em>'. $activeObject->descriptor .'</em> was saved successfully.'.$noticeExtra;
					if (isset($relation)) {
						Yii::$app->response->trigger = [
							['refresh', '.model-'. $primaryModel::baseClassName()]
						];
						Yii::$app->response->task = 'status';
					} else {
						Yii::$app->response->redirect = $niceModels['primary']['model']->getUrl('view');
					}
				}
			}
			
			if (!($this->params['form'] = $handler->getForm($models, ['subform' => $subform, 'linkExisting' => false]))) {
				throw new HttpException(403, "There is nothing to update for {$type->title->getPlural(true)}");
			}
		}
	}


	/**
	 *
	 */
	public function actionAccess() {
		$subform = null;
		$this->_parseParams(['activeObject', 'activeType'], 'read');
		extract($this->params);
		$primaryModel = $activeType->primaryModel;
		$this->params['errors'] = [];
		Yii::$app->response->view = 'access';
		$taskOptions = ['title' => 'Access for '. $activeType->title->getSingular(true)];
		$lookAtPost = false;
		if ($activeObject->can('manageAccess')) {
			$lookAtPost = true;
			$taskOptions['title'] = 'Manage ' . $taskOptions['title'];
			$taskOptions['isForm'] = false;
		}
		$this->params['access'] = $access = $activeObject->objectAccess;
		$this->params['disableFields'] = !$lookAtPost;
		$taskOptions['isForm'] = $lookAtPost;
		$objectRoles = $access->roleObjects;
		Yii::$app->response->task = 'dialog';
		Yii::$app->response->taskOptions = $taskOptions;
		if ($lookAtPost && !empty($_POST['roles'])) {
			$result = $access->save($_POST['roles']);
			if (!empty($result['errors'])) {
				if (is_array($result['errors'])) {
					$this->params['errors'] = $result['errors'];
					Yii::$app->response->error = 'An error occurred while saving the object\'s sharing settings.';
				} else {
					Yii::$app->response->error = $result['errors'];
				}
				foreach ($result['data'] as $requestorId => $roleId) {
					$objectRole = $access->getRoleObject($requestorId, $roleId);
					if (!isset($objectRoles[$requestorId]) 
							|| (isset($objectRoles[$requestorId]['role']) 
									&& $objectRoles[$requestorId]['role']->object->primaryKey !== $roleId)
					) {
						$objectRoles[$requestorId] = $objectRole;
					}
				}
			} else {
				Yii::$app->response->task = 'status';
				Yii::$app->response->success = 'Access has been updated.';
				if (empty($relatedObject)) {
					Yii::$app->response->refresh = true;
				} else {
					$primaryModel = $relatedType->primaryModel;
					Yii::$app->response->trigger = [
						['refresh', '.model-'. $primaryModel::baseClassName()]
					];
				}
			}
		}
		$this->params['objectRoles'] = $objectRoles;
	}

	/**
	 *
	 */
	public function actionDelete() {
		$subform = null;
		$this->_parseParams(['object', 'type'], 'delete');
		extract($this->params);
		if (isset($relatedType)) {
			$primaryModel = $relatedType->primaryModel;
		} else {
			$primaryModel = $type->primaryModel;
		}

		Yii::$app->response->view = 'delete';
		Yii::$app->response->task = 'dialog';
		Yii::$app->response->taskOptions = ['title' => 'Delete '. $type->title->getSingular(true) , 'isConfirmDeletion' => true];

		$this->params['model'] = new DeleteForm;
		if (isset($relation)) {
			$this->params['model']->object = $relatedObject;
			$this->params['model']->relationModel = $relation;
			$this->params['model']->relationshipWith = $object;
			$this->params['model']->forceObjectDelete = !$object->allowRogue($relation);
			if (!$this->params['model']->forceObjectDelete) {
				$this->params['model']->target = 'relationship';
			}
		} else {
			$this->params['model']->object = $object;
			$this->params['model']->forceObjectDelete = !$object->allowRogue();
		}
		if (!empty($_POST['DeleteForm'])) {
			Yii::$app->response->task = 'status';
			$targetDescriptor = $this->params['model']->targetDescriptor;
			$this->params['model']->attributes = $_POST['DeleteForm'];
			if (!$this->params['model']->delete()) {
				Yii::$app->response->error =  'Could not delete '. $this->params['model']->targetDescriptor;
			} else {
				Yii::$app->response->success = ucfirst($targetDescriptor). ' has been deleted!';
				if (!empty($_GET['redirect'])) {
					Yii::$app->response->redirect = $_GET['redirect'];
				} else {
					Yii::$app->response->trigger = [
						['refresh', '.model-'. $primaryModel::baseClassName()]
					];
				}
			}
		}
	}

	/**
	 *
	 */
	public function actionWatch() {
		if (empty($_GET['id']) or !($object = $this->params['object'] = Registry::getObject($_GET['id'], false)) or !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
			throw new HttpException(404, "Unknown object.");
		}
		if (!$object->can('read')) {
			throw new HttpException(403, "Unable to access object.");
		}
		Yii::$app->request->object = $object;

		$watching = empty($_GET['stop']);
		if ($object->watch($watching)) {
			Yii::$app->response->task = 'trigger';
			if ($watching) {
				Yii::$app->response->success = 'You are now watching '. $object->descriptor .'.';
				Yii::$app->response->trigger = [
					['startedWatching']
				];
			} else {
				Yii::$app->response->success = 'You stopped watching '. $object->descriptor .'.';
				Yii::$app->response->trigger = [
					['stoppedWatching']
				];
			}
		} else {
			Yii::$app->response->error = 'Unable update the watching status of this object.';
		}
	}



	/**
	 *
	 */
	public function actionWidget() {
		$package = [];
		$renderWidgets = [];
		if (!empty($_POST['widgets'])) {
			$renderWidgets = $_POST['widgets'];
			$baseState = ['fetch' => 0];
		} elseif (!empty($_GET['widgets'])) {
			$renderWidgets = $_GET['widgets'];
			$baseState = ['fetch' => 1];
			ob_start();
			ob_implicit_flush(false);
		}
		$sectionCount = count($renderWidgets);
		if (isset($_GET['sectionCount'])) {
			$sectionCount = (int) $_GET['sectionCount'];
		}
		if (isset($_POST['sectionCount'])) {
			$sectionCount = (int) $_POST['sectionCount'];
		}
		if (!empty($renderWidgets)) {
			foreach ($renderWidgets as $i => $widget) {
				$w = [];
				if (empty($widget['state'])) { $widget['state'] = []; }
				if (empty($widget['data'])) { $widget['data'] = []; }
				if (!isset($widget['data']['sectionCount'])) {
					$widget['data']['sectionCount'] = $sectionCount;
				}
				$w['rendered'] = Yii::$app->widgetEngine->build($widget['name'], $widget['data'], [], array_merge($baseState, $widget['state']));
				$w['id'] =  Yii::$app->widgetEngine->lastBuildId;
				$package[$i] = $w;
			}
		}
		//sleep(3);
		$this->params['widgets'] = $package;
		//var_dump($package);exit;
		$this->json();
	}
}
