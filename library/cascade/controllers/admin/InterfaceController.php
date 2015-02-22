<?php

namespace cascade\controllers\admin;

use Yii;
use yii\filters\AccessControl;
use infinite\web\Controller;
use infinite\caching\Cacher;
use infinite\base\exceptions\HttpException;
use cascade\models\DataInterface;
use cascade\models\DataInterfaceLog;

class InterfaceController extends Controller
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
        Yii::$app->response->view = 'index';
    }

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

        $log = new DataInterfaceLog;
        $log->data_interface_id = $dataInterface->primaryKey;
        if ($log->save()) {
            Yii::$app->response->success = 'Data interface action has been started';
            Yii::$app->response->redirect = ['/admin/interface/view-log', 'id' => $log->primaryKey];
            return;
        }
        Yii::$app->response->error = 'An error occurred while starting the data interface action.';
        Yii::$app->response->refresh = true;
    }


    public function actionViewLogs()
    {
        if (empty($_GET['id']) || !($dataInterface = DataInterface::get($_GET['id']))) {
            throw new HttpException(404, 'Data interface could not be found');
        }
        $this->params['dataInterface'] = $dataInterface;
        Yii::$app->response->view = 'view_logs';
    }

    public function actionViewLog()
    {
        if (empty($_GET['id']) || !($dataInterfaceLog = DataInterfaceLog::get($_GET['id']))) {
            throw new HttpException(404, 'Data interface log could not be found');
        }
        $this->params['dataInterfaceLog'] = $dataInterfaceLog;
        if ($dataInterfaceLog->status === 'queued') {
            Yii::$app->response->view = 'view_log_queued';
        } else {
            Yii::$app->response->view = 'view_log';
        }
    }
}
