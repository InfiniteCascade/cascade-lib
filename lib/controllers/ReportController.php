<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\controllers;

use canis\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ReportController [[@doctodo class_description:cascade\controllers\ReportController]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ReportController extends Controller
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
                'class' => 'yii\\web\\ErrorAction',
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
}
