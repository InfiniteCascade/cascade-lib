<?php

namespace cascade\controllers\admin;

use teal\web\Controller;
use Yii;
use yii\filters\AccessControl;

/**
 * DashboardController [[@doctodo class_description:cascade\controllers\admin\DashboardController]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DashboardController extends Controller
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
     * [[@doctodo method_description:actionTasks]].
     *
     * @return [[@doctodo return_type:actionTasks]] [[@doctodo return_description:actionTasks]]
     */
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

    /**
     * Get tasks.
     *
     * @return [[@doctodo return_type:getTasks]] [[@doctodo return_description:getTasks]]
     */
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
