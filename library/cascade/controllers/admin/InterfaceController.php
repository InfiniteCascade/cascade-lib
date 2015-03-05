<?php

namespace cascade\controllers\admin;

use cascade\components\dataInterface\DeferredAction;
use cascade\models\DataInterface;
use cascade\models\DataInterfaceLog;
use infinite\base\exceptions\HttpException;
use infinite\web\Controller;
use Yii;
use yii\filters\AccessControl;

/**
 * InterfaceController [[@doctodo class_description:cascade\controllers\admin\InterfaceController]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class InterfaceController extends Controller
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
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->gk->is('administrators');
                        },
                    ],
                    [
                        'allow' => false,
                    ],
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
     */
    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }

    /**
     * [[@doctodo method_description:actionRun]].
     *
     * @throws HttpException         [[@doctodo exception_description:HttpException]]
     * @throws NotFoundHttpException [[@doctodo exception_description:NotFoundHttpException]]
     * @return [[@doctodo return_type:actionRun]] [[@doctodo return_description:actionRun]]
     *
     */
    public function actionRun()
    {
        if (empty($_GET['id']) || !($dataInterface = DataInterface::get($_GET['id']))) {
            throw new HttpException(404, 'Data interface could not be found');
        }
        $lastLog = $dataInterface->lastDataInterfaceLog;
        if (!empty($lastLog) && $lastLog->isActive) {
            Yii::$app->response->error = 'There is already an active interface action.';
            Yii::$app->response->refresh = true;

            return;
        }

        $log = new DataInterfaceLog();
        $log->data_interface_id = $dataInterface->primaryKey;
        if (!$log->save()) {
            Yii::$app->response->error = 'An error occurred while starting the data interface log.';
            Yii::$app->response->refresh = true;

            return;
        }

        $deferredAction = DeferredAction::setup(['logModel' => $log->primaryKey]);
        if (!$deferredAction) {
            throw new NotFoundHttpException("Deferred action could not be started!");
        }
        Yii::$app->response->task = 'client';
        Yii::$app->response->clientTask = 'deferredAction';
        Yii::$app->response->taskOptions = $deferredAction->package();
    }

    /**
     * [[@doctodo method_description:actionViewLogs]].
     *
     * @throws HttpException [[@doctodo exception_description:HttpException]]
     */
    public function actionViewLogs()
    {
        if (empty($_GET['id']) || !($dataInterface = DataInterface::get($_GET['id']))) {
            throw new HttpException(404, 'Data interface could not be found');
        }
        $this->params['dataInterface'] = $dataInterface;
        Yii::$app->response->view = 'view_logs';
    }

    /**
     * [[@doctodo method_description:actionViewLog]].
     *
     * @throws HttpException [[@doctodo exception_description:HttpException]]
     * @return [[@doctodo return_type:actionViewLog]] [[@doctodo return_description:actionViewLog]]
     *
     */
    public function actionViewLog()
    {
        if (empty($_GET['id']) || !($dataInterfaceLog = DataInterfaceLog::get($_GET['id']))) {
            throw new HttpException(404, 'Data interface log could not be found');
        }
        $this->params['dataInterfaceLog'] = $dataInterfaceLog;
        if (Yii::$app->request->isAjax && !empty($_GET['package'])) {
            Yii::$app->response->data = $dataInterfaceLog->dataPackage;

            return;
        } elseif (Yii::$app->request->isAjax) {
            Yii::$app->response->taskOptions = ['title' => 'View Log', 'modalClass' => 'modal-xl'];
            Yii::$app->response->task = 'dialog';
        }
        if ($dataInterfaceLog->status === 'queued') {
            Yii::$app->response->view = 'view_log_queued';
        } else {
            Yii::$app->response->view = 'view_log';
        }
    }
}
