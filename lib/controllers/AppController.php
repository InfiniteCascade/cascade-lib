<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\controllers;

use cascade\models\LoginForm;
use cascade\models\Registry;
use canis\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * AppController [[@doctodo class_description:cascade\controllers\AppController]].
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
            ],
        ];
    }

    /**
     * [[@doctodo method_description:actionIndex]].
     *
     * @return [[@doctodo return_type:actionIndex]] [[@doctodo return_description:actionIndex]]
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * [[@doctodo method_description:actionLogin]].
     *
     * @return [[@doctodo return_type:actionLogin]] [[@doctodo return_description:actionLogin]]
     */
    public function actionLogin()
    {
        $this->params['model'] = $model = new LoginForm();
        if ($model->load($_POST) && $model->login()) {
            //Yii::$app->response->redirect = Yii::$app->getUser()->getReturnUrl();
            Yii::$app->session->setFlash('delayed-instructions', json_encode(['pauseTimer' => false]));

            return $this->goBack();
        } else {
            Yii::$app->response->task = 'dialog';
            Yii::$app->response->taskOptions = ['title' => 'Log In'];
            Yii::$app->response->view = 'login';
            Yii::$app->response->baseInstructions['pauseTimer'] = true;
        }
    }

    /**
     * [[@doctodo method_description:actionLogout]].
     *
     * @return [[@doctodo return_type:actionLogout]] [[@doctodo return_description:actionLogout]]
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * [[@doctodo method_description:actionActivity]].
     */
    public function actionActivity()
    {
        // Yii::$app->response->baseInstructions['timestamp'] = time();
        Yii::$app->response->forceInstructions = true;
        Yii::$app->response->task = 'status';

        $auditClass = Yii::$app->classes['Audit'];
        $provider = $auditClass::activityDataProvider();
        $provider->handleInstructions($_POST);
        if (Yii::$app->request->object) {
            $provider->context = Yii::$app->request->object->primaryKey;
        }
        Yii::$app->response->baseInstructions = $provider->package->toArray();
    }

    /**
     * [[@doctodo method_description:actionRefresh]].
     *
     * @return [[@doctodo return_type:actionRefresh]] [[@doctodo return_description:actionRefresh]]
     */
    public function actionRefresh()
    {
        $refreshed = [];
        Yii::$app->response->baseInstructions['requests'] = &$refreshed;
        Yii::$app->response->forceInstructions = true;
        Yii::$app->response->task = 'status';

        if (empty($_POST['requests'])) {
            return;
        }
        $baseInstrictions = (isset($_POST['baseInstructions']) ? $_POST['baseInstructions'] : []);
        foreach ($_POST['requests'] as $requestId => $request) {
            $refreshed[$requestId] = false;
            $instructions = $baseInstrictions;
            if (isset($request['instructions'])) {
                $instructions = array_merge($instructions, $request['instructions']);
            }
            if (empty($instructions['type']) || empty($instructions['type'])) {
                continue;
            }
            if (isset($request['state'])) {
                foreach ($request['state'] as $key => $value) {
                    Yii::$app->webState->set($key, $value);
                }
            }

            if (isset($instructions['objectId'])) {
                $object = Yii::$app->request->object = Registry::getObject($instructions['objectId']);
                if (!$object) {
                    $refreshed[$requestId] = ['error' => 'Invalid object ' . $instructions['objectId'] . ''];
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
     * [[@doctodo method_description:actionStream]].
     *
     * @return [[@doctodo return_type:actionStream]] [[@doctodo return_description:actionStream]]
     */
    public function actionStream()
    {
        header("Content-type: text/plain");
        header("Access-Control-Allow-Origin: *");
        ob_implicit_flush(1);

        Yii::$app->response->task = false;
        $refreshed = [];
        $source = $_GET;
        if (isset($source['when']) && $source['when'] === 'handshake') {
            echo json_encode(['id' => md5(microtime(true)), 'transports' => ['stream']]);

            return;
        }
        if (empty($source['requests'])) {
            echo json_encode([]);

            return;
        }
        $baseInstrictions = (isset($source['baseInstructions']) ? $source['baseInstructions'] : []);
        foreach ($source['requests'] as $requestId => $request) {
            $refreshed[$requestId] = false;
            $instructions = $baseInstrictions;
            if (isset($request['instructions'])) {
                $instructions = array_merge($instructions, $request['instructions']);
            }
            if (empty($instructions['type']) || empty($instructions['type'])) {
                continue;
            }
            if (isset($request['state'])) {
                foreach ($request['state'] as $key => $value) {
                    Yii::$app->webState->set($key, $value);
                }
            }

            if (isset($instructions['objectId'])) {
                $object = Yii::$app->request->object = Registry::getObject($instructions['objectId']);
                if (!$object) {
                    $refreshed[$requestId] = ['error' => 'Invalid object ' . $instructions['objectId'] . ''];
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
                echo "data: " . json_encode(['type' => 'handleRequests', 'data' => [$requestId => $refreshed[$requestId]], 'id' => round(microtime(true) * 100)]);
                echo "\n\n";
                //echo str_repeat("\n\n",1024*4);
            }
        }
        ob_implicit_flush(0);
        ob_start();
        Yii::$app->end();
        ob_end_clean();
    }
}
