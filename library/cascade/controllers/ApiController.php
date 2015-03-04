<?php
/**
 * @link http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cascade\controllers;

use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
use yii\rest\Controller;

class ApiController extends Controller
{
    public $modelClass;
    public $updateScenario = Model::SCENARIO_DEFAULT;
    public $createScenario = Model::SCENARIO_DEFAULT;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (isset($_GET['type'])
            && ($typeItem = Yii::$app->collectors['types']->getOne($_GET['type']))
            && ($type = $typeItem->object)
            && !empty($type)
            && $type->enableApiAccess) {
            $this->modelClass = $type->primaryModel;
        }

        if ($this->modelClass === null) {
            throw new ForbiddenHttpException('Unable to access the object type \''.(isset($_GET['type']) ? $_GET['type'] : 'unknown').'\'.');
        }
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model  the model to be accessed. If null, it means no specific model is being accessed.
     * @param array  $params additional parameters
     *
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'index') {
        }
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'cascade\components\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'cascade\components\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => 'cascade\components\rest\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'cascade\components\rest\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'cascade\components\rest\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'cascade\components\rest\OptionsAction',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }
}
