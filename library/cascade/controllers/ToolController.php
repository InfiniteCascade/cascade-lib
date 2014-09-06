<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\controllers;

use Yii;

use infinite\helpers\ArrayHelper;
use infinite\web\Controller;
use infinite\db\ActiveRecord;
use infinite\base\exceptions\HttpException;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ObjectController [@doctodo write class description for ObjectController]
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
            ]
        ];
    }

    public function actionIndex()
    {
        Yii::$app->response->view = 'index';

    }
}