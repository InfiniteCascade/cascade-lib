<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\controllers;

use canis\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ToolController [[@doctodo class_description:cascade\controllers\ToolController]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ToolController extends Controller
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
