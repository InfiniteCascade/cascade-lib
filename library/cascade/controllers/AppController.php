<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use infinite\web\Controller;

use cascade\models\LoginForm;
use cascade\models\Registry;

/**
 * AppController [@doctodo write class description for AppController]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AppController extends Controller
{
    /**
    * @inheritdoc
     */
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
                        'actions' => ['refresh', 'stream', 'index', 'activity'],
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

    /**
    * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * __method_actionIndex_description__
     * @return __return_actionIndex_type__ __return_actionIndex_description__
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * __method_actionLogin_description__
     * @return __return_actionLogin_type__ __return_actionLogin_description__
     */
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

    /**
     * __method_actionLogout_description__
     * @return __return_actionLogout_type__ __return_actionLogout_description__
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionActivity()
    {
        Yii::$app->response->baseInstructions['timestamp'] = time();
        Yii::$app->response->forceInstructions = true;
        Yii::$app->response->task = 'status';

        $auditClass = Yii::$app->classes['Audit'];
        $provider = $auditClass::activityDataProvider();
        $provider->handleInstructions($_POST);
        Yii::$app->response->baseInstructions['activity'] = $provider->package();
    }

    /**
     * __method_actionRefresh_description__
     * @return __return_actionRefresh_type__ __return_actionRefresh_description__
     */
    public function actionRefresh()
    {
        $refreshed = [];
        Yii::$app->response->baseInstructions['requests'] = &$refreshed;
        Yii::$app->response->forceInstructions = true;
        Yii::$app->response->task = 'status';

        if (empty($_POST['requests'])) { return; }
        $baseInstrictions = (isset($_POST['baseInstructions']) ? $_POST['baseInstructions'] : []);
        foreach ($_POST['requests'] AS $requestId => $request) {
            $refreshed[$requestId] = false;
            $instructions = $baseInstrictions;
            if (isset($request['instructions'])) {
                $instructions = array_merge($instructions, $request['instructions']);
            }
            if (empty($instructions['type']) || empty($instructions['type'])) { continue; }
            if (isset($request['state'])) {
                foreach ($request['state'] as $key => $value) {
                    Yii::$app->webState->set($key, $value);
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

    /**
     * __method_actionRefresh_description__
     * @return __return_actionRefresh_type__ __return_actionRefresh_description__
     */
    public function actionStream()
    {
        header("Content-type: text/plain");
        header("Access-Control-Allow-Origin: *");
        ob_implicit_flush(1);

        Yii::$app->response->task = false;
        $refreshed = [];
        $source = $_GET;
        if (empty($source['requests'])) { return; }
        $baseInstrictions = (isset($source['baseInstructions']) ? $source['baseInstructions'] : []);
        foreach ($source['requests'] AS $requestId => $request) {
            $refreshed[$requestId] = false;
            $instructions = $baseInstrictions;
            if (isset($request['instructions'])) {
                $instructions = array_merge($instructions, $request['instructions']);
            }
            if (empty($instructions['type']) || empty($instructions['type'])) { continue; }
            if (isset($request['state'])) {
                foreach ($request['state'] as $key => $value) {
                    Yii::$app->webState->set($key, $value);
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
            if ($refreshed[$requestId]) {
                echo "data: ".json_encode(['type' => 'handleRequests', 'data' => [$requestId => $refreshed[$requestId]], 'id' => round(microtime(true) * 100)]) ."\n";
                echo str_repeat("\n",1024*4);
            }
        }
        ob_implicit_flush(0);
        ob_start();
        Yii::$app->end();
        ob_end_clean();
    }
}
