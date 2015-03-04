<?php
namespace cascade\components\rest;

use Yii;
use yii\base\InvalidParamException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class IndexAction extends \yii\rest\IndexAction
{
    use ActionTrait;

    public $parentObject;
    protected $_dataProvider;

    public function getRequiredParams()
    {
        $requiredParams = parent::getRequiredParams();
        $modelClass = $this->modelClass;
        $objectType = (new $modelClass())->objectType;
        if (empty($objectType)) {
            throw new InvalidParamException($modelClass . ' does not have a corresponding object type');
        }
        if (!$objectType->hasDashboard) {
            $requiredParams[] = 'parentObject';
        }

        return $requiredParams;
    }

    public function params()
    {
        return ['parentObject'];
    }

    protected function prepareDataProvider()
    {
        if (!isset($this->_dataProvider)) {
            $modelClass = $this->modelClass;
            $dummyModel = new $modelClass();
            $this->_dataProvider = $dataProvider = parent::prepareDataProvider();
            $this->_dataProvider->sort->attributes['descriptor'] = [
                'label' => 'Descriptor',
                'asc' => $dummyModel->getDescriptorDefaultOrder($dummyModel->tableName(), SORT_ASC),
                'desc' => $dummyModel->getDescriptorDefaultOrder($dummyModel->tableName(), SORT_DESC),
            ];
            $this->_dataProvider->sort->defaultOrder = ['descriptor' => SORT_ASC];
            $objectType = $dummyModel->objectType;
            $query = false;
            if (!empty(Yii::$app->request->queryParams['query'])) {
                $query = $dataProvider->query->buildContainsQuery(Yii::$app->request->queryParams['query']);
            } elseif (!empty(Yii::$app->request->queryParams['advancedQuery'])) {
                $query = json_decode(Yii::$app->request->queryParams['advancedQuery'], true);
            }
            $whereConditions = $dataProvider->query->andWhereFromQuery($query);
            if (empty($objectType)) {
                throw new InvalidParamException($modelClass . ' does not have a corresponding object type');
            }
            if (!isset($this->parentObject)) {
                $dataProvider->query->denyInherit();
            } else {
                $registryClass = Yii::$app->classes['Registry'];
                $parentObject = $registryClass::get($this->parentObject, false);
                if (!$parentObject) {
                    throw new NotFoundHttpException("Object not found: {$this->parentObject}");
                }
                if (!$parentObject->can('read')) {
                    throw new ForbiddenHttpException("Unable to access {$this->parentObject}");
                }
                $newQuery = $parentObject->queryChildObjects($this->modelClass);
                $this->_dataProvider->query = $newQuery;
            }
        }

        return $this->_dataProvider;
    }
}
