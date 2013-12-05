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
				'only' => ['login', 'logout'],
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
						'actions' => ['refresh'],
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
					'refresh' => ['post'],
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
		$model = new LoginForm();
		if ($model->load($_POST) && $model->login()) {
			return $this->goBack();
		} else {
			return $this->render('login', [
				'model' => $model,
			]);
		}
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->goHome();
	}

	public function actionRefresh()
	{
		$refreshed = false;
		$this->response->baseInstructions['content'] = &$refreshed;
		$this->response->forceInstructions = true;
		$this->response->task = 'status';
		if (empty($_POST['instructions']) || empty($_POST['instructions']['type']) || empty($_POST['instructions']['systemId'])) { return; }
		$instructions = $_POST['instructions'];
		
		if (!empty($_POST['state'])) {
			foreach ($_POST['state'] as $key => $value) {
				Yii::$app->state->set($key, $value);
			}
		}

		if (isset($instructions['objectId'])) {
			$object = Yii::$app->request->object = Registry::getObject($instructions['objectId']);
			if (!$object) {
				throw new HttpException(404, 'Unknown object');
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
					$this->response->error = 'Unknown widget!';
					return;
				}
				$widgetObject = $widget->object;

				$widgetObject->owner = $widget->owner;
				$refreshed = $widgetObject->generate();
			break;
		}
	}
}
