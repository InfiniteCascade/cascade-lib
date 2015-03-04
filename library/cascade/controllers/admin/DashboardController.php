<?php

namespace cascade\controllers\admin;

use infinite\web\Controller;
use Yii;
use yii\filters\AccessControl;

class DashboardController extends Controller
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
            ],
        ];
    }

    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }

    public function actionTasks()
    {
        $tasks = $this->params['tasks'] = $this->getTasks();
        if (isset($_GET['task'])) {
            Yii::$app->response->task = 'message';
            if (isset($tasks[$_GET['task']])) {
                $tasks[$_GET['task']]['run']();
            } else {
                Yii::$app->response->content = 'Unknown task!';
                Yii::$app->response->taskOptions = ['state' => 'danger'];
            }

            return;
        }

        Yii::$app->response->view = 'tasks';
    }

    protected function getTasks()
    {
        $tasks = [];
        $tasks['flush-file-cache'] = [];
        $tasks['flush-file-cache']['title'] = 'Flush File Cache';
        $tasks['flush-file-cache']['description'] = 'Clear the file cache in Cascade';
        $tasks['flush-file-cache']['run'] = function () {
            Yii::$app->fileCache->flush();
            Yii::$app->response->content = 'File cache was flushed!';
            Yii::$app->response->taskOptions = ['state' => 'success', 'title' => 'Success'];
        };

        $tasks['flush-cache'] = [];
        $tasks['flush-cache']['title'] = 'Flush Memory Cache';
        $tasks['flush-cache']['description'] = 'Clear the memory cache in Cascade';
        $tasks['flush-cache']['run'] = function () {
            Yii::$app->cache->flush();
            Yii::$app->response->content = 'Memory cache was flushed!';
            Yii::$app->response->taskOptions = ['state' => 'success', 'title' => 'Success'];
        };

        return $tasks;
    }
}
