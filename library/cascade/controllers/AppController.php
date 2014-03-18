<?php

namespace cascade\controllers;

use Yii;
use yii\web\AccessControl;
use yii\web\VerbFilter;

use infinite\web\Controller;
use infinite\base\exceptions\HttpException;

use cascade\models\LoginForm;
use cascade\models\Registry;

class AppController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => ['login'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'actions' => ['refresh', 'index'],
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
					'refresh' => ['get'],
				],
			],
		];
	}

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			]
		];
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionLogin()
	{
		$this->params['model'] = $model = new LoginForm();
		if ($model->load($_POST) && $model->login()) {
			//Yii::$app->response->redirect = Yii::$app->getUser()->getReturnUrl();
			return $this->goBack();
		} else {
			Yii::$app->response->task = 'dialog';
			Yii::$app->response->taskOptions = ['title' => 'Log In'];
			Yii::$app->response->view = 'login';
		}
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->goHome();
	}

	public function actionRefresh()
	{
		$refreshed = [];
		Yii::$app->response->baseInstructions['requests'] = &$refreshed;
		Yii::$app->response->forceInstructions = true;
		Yii::$app->response->task = 'status';


		if (empty($_GET['requests'])) { return; }
		$baseInstrictions = (isset($_GET['baseInstructions']) ? $_GET['baseInstructions'] : []);
		foreach ($_GET['requests'] AS $requestId => $request) {
			$refreshed[$requestId] = false;
			$instructions = $baseInstrictions;
			if (isset($request['instructions'])) {
				$instructions = array_merge($instructions, $request['instructions']);
			}
			if (empty($instructions['type']) || empty($instructions['type'])) { continue; }
			if (isset($request['state'])) {
				foreach ($request['state'] as $key => $value) {
					Yii::$app->state->set($key, $value);
				}
			}

			if (isset($instructions['objectId'])) {
				$object = Yii::$app->request->object = Registry::getObject($instructions['objectId']);
				if (!$object) {
					$refreshed[$requestId] = ['error' => 'Invalid object '. $instructions['objectId'] .''];
					continue;
				}
				$type = $object->objectType;
			}

			$settings = (isset($instructions['settings'])) ? $instructions['settings'] : [];
			switch ($instructions['type']) {
				case 'widget':
					$widget = false;
					if (isset($object)) {
						$widgets = $object->objectTypeItem->widgets;
						if (isset($widgets[$instructions['systemId']])) {
							$widget = $widgets[$instructions['systemId']]->object;
						}
					} else {
						$widget = Yii::$app->collectors['widgets']->getOne($instructions['systemId']);
					}
					if (!$widget) {
						$refreshed[$requestId] = ['error' => 'Unknown widget'];
						return;
					}
					$widgetObject = $widget->object;
					if (isset($instructions['section']) 
						&& ($sectionItem = Yii::$app->collectors['sections']->getOne($instructions['section']))
						&& ($section = $sectionItem->object)) {
						$widgetObject->attachDecorator($section->widgetDecoratorClass);
						$widgetObject->section = $section;
					}
					$widgetObject->owner = $widget->owner;
					$refreshed[$requestId] = ['content' => $widgetObject->generate()];
				break;
			}
		}
	}
}
