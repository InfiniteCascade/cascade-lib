<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\controllers;

use Yii;

use cascade\models\Registry;
use cascade\models\Relation;
use cascade\models\ObjectFamiliarity;
use cascade\models\DeleteForm;
use cascade\components\types\Module as TypeModule;
use cascade\components\types\Relationship;
use cascade\components\web\ObjectViewEvent;
use cascade\components\web\browser\Response as BrowseResponse;

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
class ObjectController extends Controller
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
                    'view' => ['get'],
                    'create' => ['get', 'post'],
                    'setPrimary' => ['get'],
                    'link' => ['get', 'post'],
                    'setPrimary' => ['get'],
                    'update' => ['get', 'post'],
                    'updateField' => ['post'],
                    'delete' => ['get', 'post'],
                    'watch' => ['get'],
                    'unwatch' => ['get'],
                    'widget' => ['get', 'post'],
                    'unwatch' => ['get'],
                    'search' => ['get'],
                    'profile' => ['get'],
                    'browse' => ['get'],
                    'activity' => ['get'],
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

    /**
     * __method_actionBrowse_description__
     * @return __return_actionBrowse_type__ __return_actionBrowse_description__
     */
    public function actionBrowse()
    {
        $package = [];
        $defaultParams = [];
        $requestParams = array_merge($defaultParams, $_GET);

        if (empty($requestParams['requests'])) {
            Yii::$app->response->data = $package;

            return;
        }

        if (isset($requestParams['modules']) && !is_array($requestParams['modules'])) {
            $requestParams['modules'] = implode(',', $requestParams['modules']);
        }

        $modules = isset($requestParams['modules']) ? (array) $requestParams['modules'] : false;
        $baseInstructions = ['ignore' => [], 'ignoreChildren' => [], 'ignoreParents' => []];
        if (!empty($searchParams['ignore'])) {
            $baseInstructions['ignore'] = (array) $searchParams['ignore'];
        }
        if (!empty($searchParams['ignoreChildren'])) {
            $baseInstructions['ignoreChildren'] = (array) $searchParams['ignoreChildren'];
        }
        if (!empty($searchParams['ignoreParents'])) {
            $baseInstructions['ignoreParents'] = (array) $searchParams['ignoreParents'];
        }
        $baseInstructions['modules'] = $modules;
        $requests = [];
        foreach ($requestParams['requests'] as $requestId => $request) {
            if (isset($request['task']) && !isset($request['handler'])) {
                // we're doing something new
                switch ($request['task']) {
                    case 'root':
                        $request['handler'] = 'types';
                    break;
                    case 'stack':
                        $request = BrowseResponse::parseStack(array_merge($baseInstructions, $request));
                    break;
                }
            }

            if ($request && isset($request['handler'])) {
                $requests[$requestId] = $request;
            }
        }
        $package = BrowseResponse::handleRequests($requests, $baseInstructions, true)->package();
        Yii::$app->response->data = $package;
    }


    /**
     * __method_actionSearch_description__
     * @return __return_actionSearch_type__ __return_actionSearch_description__
     */
    public function actionSearch()
    {
        $package = [];
        $defaultParams = ['typeFilters' => []];
        $searchParams = array_merge($defaultParams, $_GET);

        if (empty($searchParams['term'])) {
            Yii::$app->response->data = $package;

            return;
        }

        if (isset($searchParams['modules']) && !is_array($searchParams['modules'])) {
            $searchParams['modules'] = implode(',', $searchParams['modules']);
        }

        $modules = isset($searchParams['modules']) ? (array) $searchParams['modules'] : array_keys(Yii::$app->collectors['types']->getAll());
        $params = ['ignore' => [], 'ignoreChildren' => [], 'ignoreParents' => []];
        if (!empty($searchParams['ignore'])) {
            $params['ignore'] = (array) $searchParams['ignore'];
        }
        if (!empty($searchParams['ignoreChildren'])) {
            $params['ignoreChildren'] = (array) $searchParams['ignoreChildren'];
        }
        if (!empty($searchParams['ignoreParents'])) {
            $params['ignoreParents'] = (array) $searchParams['ignoreParents'];
        }
        $params['action'] = 'read';
        $params['modules'] = $modules;
        $term = $searchParams['term'];
        $params['limit'] = 8;
        $scores = [];
        foreach ($modules as $module) {
            $moduleItem = Yii::$app->collectors['types']->getOne($module);
            if (!$moduleItem || !($moduleObject = $moduleItem->object)) { continue; }
            if (empty($moduleItem->object->searchWeight)) { continue; }
            if (in_array('authority', $searchParams['typeFilters']) && $moduleItem->object->getBehavior('Authority') === null) { continue; }
            if (in_array('dashboard', $searchParams['typeFilters']) && !$moduleItem->object->hasDashboard) { continue; }
            $moduleResults = $moduleObject->search($term, $params);
            foreach ($moduleResults as $r) {
                $package[] = $r;
            }
        }
        ArrayHelper::multisort($package, 'scoreSort', SORT_DESC);
        foreach (array_slice($package, 0, 30) as $key => $result) {
            $package[$key] = $result->toArray();
        }
        Yii::$app->response->data = $package;
    }

    /**
     * __method_actionView_description__
     * @return __return_actionView_type__ __return_actionView_description__
     * @throws HttpException __exception_HttpException_description__
     */
    public function actionPhoto()
    {
        if (empty($_GET['id']) or !($object = $this->params['object'] = Registry::getObject($_GET['id'], false)) or !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
            throw new HttpException(404, "Unknown object.");
        }
        if (!$object->can('read')) {
            throw new HttpException(403, "Unable to access object.");
        }
        Yii::$app->request->object = $object;
        
        if ($object->getBehavior('Photo') === null) {
            throw new HttpException(404, "No profile photo available (A)");
        }

        if (!$object->serve()) {
            throw new HttpException(404, "No profile photo available (B)");
        }
    }


    /**
     * __method_actionView_description__
     * @return __return_actionView_type__ __return_actionView_description__
     * @throws HttpException __exception_HttpException_description__
     */
    public function actionView()
    {
        if (empty($_GET['id']) or !($object = $this->params['object'] = Registry::getObject($_GET['id'], false)) or !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
            throw new HttpException(404, "Unknown object.");
        }
        if (!$object->can('read')) {
            throw new HttpException(403, "Unable to access object.");
        }
        $action = isset($_GET['subaction']) ? $_GET['subaction'] : 'view';
        Yii::$app->request->object = $object;
        $object->loadChildParentIds();
        $type = $this->params['type'] = $object->objectType;
        $viewEvent = new ObjectViewEvent(['object' => $object, 'action' => $action]);
        $type->trigger(TypeModule::EVENT_VIEW_OBJECT, $viewEvent);
        Yii::$app->collectors['widgets']->lazy = true;
        
        if ($viewEvent->handled) {
            if ($viewEvent->accessed) {
                ObjectFamiliarity::accessed($object);
            }
            return;
        }


        if (empty($_GET['h']) or !($relatedObject = $this->params['relatedObject'] = Registry::getObject($_GET['h'], false)) or !($relatedTypeItem = $this->params['relatedTypeItem'] = $relatedObject->objectTypeItem)) {
            $relatedObject = null;
        } elseif (!$object->can('read')) {
            $relatedObject = null;
        }

        if (!$type->hasDashboard) {
            $relatedObjectOptions = [];
            $relatedObjects = $object->queryRelations(false)->all();
            foreach ($relatedObjects as $relation) {
                if ($relation->child_object_id === $object->primaryKey) {
                    $relatedTest = Registry::getObject($relation->parent_object_id, false);
                } else {
                    $relatedTest = Registry::getObject($relation->child_object_id, false);
                }
                if (!$relatedTest || !$relatedTest->objectType->hasDashboard || !$relatedTest->can('read')) { continue; }
                $relatedObjectOptions[$relatedTest->primaryKey] = ['descriptor' => $relatedTest->descriptor, 'url' => $relatedTest->getUrl('view', ['h' => $object->primaryKey], false)];
            }
            if (isset($relatedObject) && isset($relatedObjectOptions[$relatedObject->primaryKey])) {
                $this->redirect($relatedObjectOptions[$relatedObject->primaryKey]['url']);
                return;
            } elseif (sizeof($relatedObjectOptions) === 1) {
                $relatedObject = array_pop($relatedObjectOptions);
                $this->redirect($relatedObject['url']);
                return;
            } else {
                $this->params['options'] = $relatedObjectOptions;
                Yii::$app->response->view = 'viewOptions';
                return;
            }
            throw new HttpException(400, "Bad request");
        }
        $this->params['highlight'] = $relatedObject;

        ObjectFamiliarity::accessed($object);
        Yii::$app->response->view = 'view';

        $sections = $this->params['sections'] = $typeItem->getSections($object);
        $this->params['active'] = $this->params['default'] = null;
        foreach ($sections as $section) {
            if ($section->priority > 0) {
                $this->params['active'] = $this->params['default'] = $section->systemId;
                break;
            }
        }
        if (!empty($_GET['section'])) {
            $this->params['active'] = $_GET['section'];
        }
    }

    /**
     * __method_actionView_description__
     * @return __return_actionView_type__ __return_actionView_description__
     * @throws HttpException __exception_HttpException_description__
     */
    public function actionActivity()
    {
        if (empty($_GET['id']) or !($object = $this->params['object'] = Registry::getObject($_GET['id'], false)) or !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
            throw new HttpException(404, "Unknown object.");
        }
        if (!$object->can('read')) {
            throw new HttpException(403, "Unable to access object.");
        }
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => 'Activity for ' . $object->descriptor , 'width' => '100%', 'isForm' => false];
        Yii::$app->response->view = 'activity';
        Yii::$app->request->object = $object;
    }

    public function _checkParams($params, $required = [])
    {
        if (in_array('type', $required) && isset($params['type'])) {
            if (!$params['type']->hasDashboard) {
                $required[] = 'relatedObject';
                $required[] = 'relationship';
                $required[] = 'relatedType';
                $required[] = 'relationshipRole';
            }
        }

        $required = array_unique($required);
        foreach ($required as $r) {
            if (empty($params[$r])) {
                throw new HttpException(400, "Invalid request ({$r} is required)");
            }
        }
        return true;
    }

    public function _parseParams()
    {
        $paramSource = $_GET;
        $p = [];

        // primary object
        $p['objectId'] = ArrayHelper::getValue($paramSource, 'id', false);
        if ($p['objectId']) {
            $p['object'] = Registry::getObject($p['objectId'], false);
            if (empty($p['object'])) {
                throw new HttpException(404, "Unknown object '{$p['objectId']}.");
            }
        }

        // object type
        $p['typeName'] = ArrayHelper::getValue($paramSource, 'type', false);
        if ($p['typeName']) {
            $p['typeItem'] = Yii::$app->collectors['types']->getOne($p['typeName']);
            if (isset($p['typeItem']->object)) {
                $p['type'] = $p['typeItem']->object;
            }
        } elseif (isset($p['object'])) {
            $p['typeItem'] = $p['object']->objectTypeItem;
            $p['type'] = $p['object']->objectType;
        }

        if (empty($p['type'])) {
            throw new HttpException(404, "Unknown object type.");
        }

        // related object
        $p['relatedObjectId'] = ArrayHelper::getValue($paramSource, 'related_object_id', false);
        if ($p['relatedObjectId']) {
            $p['relatedObject'] = Registry::getObject($p['relatedObjectId'], false);
            if (empty($p['relatedObject'])) {
                throw new HttpException(404, "Unknown related object.");
            }
            $p['relatedType'] = $p['relatedObject']->objectType;
        }

        // relation
        $p['objectRelationName'] = ArrayHelper::getValue($paramSource, 'object_relation', false);
        if ($p['objectRelationName'] && isset($p['relatedType'])) {
            list($p['relationship'], $p['relationshipRole']) = $p['relatedType']->getRelationship($p['objectRelationName']);
            if (!empty($p['relationship']) && !empty($p['relatedObject']) && !empty($p['object'])) {
                if ($p['relationshipRole'] === 'child') {
                    $p['parentObject'] = $p['relatedObject'];
                    $p['childObject'] = $p['object'];
                } else {
                    $p['parentObject'] = $p['object'];
                    $p['childObject'] = $p['relatedObject'];
                }
                $p['relation'] = $p['relationship']->getModel($p['parentObject'], $p['childObject']);
            }
            if (empty($p['relationship'])) {
                throw new HttpException(404, "Unknown type relationship {$p['objectRelationName']}");
            }
        }
        return $p;
    }

    public function actionCreate()
    {
        $p = $this->_parseParams();
        $this->_checkParams($p, ['type']);
        $this->params = &$p;
        
        if (!Yii::$app->gk->canGeneral('create', $p['type']->primaryModel)) {
            throw new HttpException(403, "You do not have access to create {$p['type']->title->getPlural(true)}");
        }

        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => 'Create ' . $p['type']->title->getSingular(true) , 'width' => '800px'];
        
        $p['primaryModel'] = $p['type']->getModel();
        $relations = [];
        if (isset($p['relatedObject'])) {
            $fields = $p['primaryModel']->getFields();
            $companionNiceId = $p['relationship']->getCompanionNiceId($p['relationshipRole']);
            if (isset($fields[$companionNiceId])) {
                if ($p['relationshipRole'] === 'child') {
                    $relationField = 'parent_object_id';
                } else {
                    $relationField = 'child_object_id';
                }
                $p['primaryModel']->setIndirectObject($p['relatedObject']);
                $fields[$companionNiceId]->model->{$relationField} = $p['relatedObject']->primaryKey;
                $relations[$fields[$companionNiceId]->model->tabularId] = $fields[$companionNiceId]->model;
            } else {
                // \d(array_keys($fields));
                // \d($p['objectRelationName']);
                throw new HttpException(403, "Invalid relationship!");
            }
        }

        $p['primaryModel']->setRelationModels($relations);
        if (!empty($_POST)) {
            $p['primaryModel']->load($_POST);
            if ($p['primaryModel']->getBehavior('Storage') !== null) {
                $p['primaryModel']->loadPostFile();
            }
            if (!$p['primaryModel']->save()) {
                Yii::$app->response->error = 'Unable to create object!';
            } else {
                Yii::$app->response->task = 'status';
                Yii::$app->response->success = '<em>'. $p['primaryModel']->descriptor .'</em> was created successfully.';
                if (isset($p['relatedType'])) {
                    $primaryModelClass = get_class($p['primaryModel']);
                    Yii::$app->response->trigger = [
                        ['refresh', '.model-'. $primaryModelClass::baseClassName()]
                    ];
                } else {
                    Yii::$app->response->redirect = $p['primaryModel']->getUrl('view');
                }
            }
        }

        if (!($p['form'] = $p['type']->getForm($p['primaryModel'], ['relationSettings' => false]))) {
            throw new HttpException(403, "There is nothing to create for {$p['type']->title->getPlural(true)}");
        }
        $p['form']->ajax = true;
    }

    public function actionUpdate()
    {
        $p = $this->_parseParams();
        $this->_checkParams($p, ['type', 'object']);
        $this->params = &$p;
        
        if (!$p['object']->can('update')) {
            throw new HttpException(403, "You do not have access to update the {$p['type']->title->getPlural(true)} '{$p['object']->descriptor}'");
        }

        if (isset($p['relatedObject']) && !$p['relatedObject']->can('update')) {
            throw new HttpException(403, "You do not have access to update '{$p['relatedObject']->descriptor}'");
        }

        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => 'Update ' . $p['type']->title->getSingular(true) , 'width' => '800px'];
        
        $p['primaryModel'] = $p['type']->getModel($p['object']);
        if (isset($p['relatedObject'])) {
            $p['primaryModel']->setIndirectObject($p['relatedObject']);
        }
        if (!empty($_POST)) {
            $p['primaryModel']->load($_POST);
            if ($p['primaryModel']->getBehavior('Storage') !== null) {
                $p['primaryModel']->loadPostFile();
            }
            if (!$p['primaryModel']->save()) {
                // \d($p['primaryModel']->errors);
                Yii::$app->response->error = 'Unable to update object!';
            } else {
                Yii::$app->response->task = 'status';
                Yii::$app->response->success = '<em>'. $p['primaryModel']->descriptor .'</em> was updated successfully.';
                if (isset($p['relatedType'])) {
                    $primaryModelClass = get_class($p['primaryModel']);
                    Yii::$app->response->trigger = [
                        ['refresh', '.model-'. $primaryModelClass::baseClassName()]
                    ];
                } else {
                    Yii::$app->response->redirect = $p['primaryModel']->getUrl('view');
                }
            }
        }

        if (!($p['form'] = $p['type']->getForm($p['primaryModel'], ['relationSettings' => false]))) {
            throw new HttpException(403, "There is nothing to update for {$p['type']->title->getPlural(true)}");
        }
        $p['form']->ajax = true;
    }

    public function actionSetPrimary()
    {
        $p = $this->_parseParams();
        $this->_checkParams($p, ['type', 'object', 'relation']);
        $this->params = &$p;
        
        if (!$p['object']->can('update')) {
            throw new HttpException(403, "You do not have access to update the {$p['type']->title->getPlural(true)} '{$p['object']->descriptor}'");
        }

        Yii::$app->response->view = false;
        Yii::$app->response->task = 'status';
        
        if (!$p['relation']->setPrimary($p['relationshipRole'])) {
            Yii::$app->response->error = 'Unable to set relationship as primary!';
        } else {
            Yii::$app->response->task = 'status';
            Yii::$app->response->success = '<em>'. $p['object']->descriptor .'</em> was set as primary!';
            
            $primaryModelClass = get_class($p['object']);
            Yii::$app->response->trigger = [
                ['refresh', '.model-'. $primaryModelClass::baseClassName()]
            ];
        }
    }

    public function actionLink()
    {
        $p = $this->_parseParams();
        $this->_checkParams($p, ['type', 'relatedObject', 'relatedType']);
        $this->params = &$p;
        
        if (isset($p['relatedObject']) && !$p['relatedObject']->can('update')) {
            throw new HttpException(403, "You do not have access to update '{$p['relatedObject']->descriptor}'");
        }

        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => 'Link ' . $p['relatedType']->title->getSingular(true) , 'width' => '800px'];
        
        $p['primaryModel'] = $p['relatedType']->getModel($p['relatedObject']);
        $relations = [];
        $relationSettings = ['template' => 'hierarchy'];
        if (isset($p['relatedObject'])) {
            $fields = $p['primaryModel']->getFields();
            $niceId = $p['relationship']->getNiceId($p['relationshipRole']);
            if (isset($fields[$niceId])) {
                if ($p['relationshipRole'] === 'child') {
                    $relationField = 'parent_object_id';
                } else {
                    $relationField = 'child_object_id';
                }
                if (isset($p['relation'])) {
                    $tabularId = $fields[$niceId]->model->tabularId;
                    $handler = $fields[$niceId]->model->_moduleHandler;
                    $fields[$niceId]->model = $p['relation'];
                    $relationSettings['lockFields'] = ['object_id'];
                    $fields[$niceId]->model->tabularId = $handler;
                    $fields[$niceId]->model->_moduleHandler = $handler;
                } else {
                    $fields[$niceId]->resetModel();
                    $fields[$niceId]->model->{$relationField} = $p['relatedObject']->primaryKey;
                }
                $relations[$fields[$niceId]->model->tabularId] = $fields[$niceId]->model;
            } else {
                throw new HttpException(403, "Invalid relationship!");
            }
        }

        $p['primaryModel']->setRelationModels($relations);
        if (!empty($_POST)) {
            $p['primaryModel']->load($_POST);
            if (!$p['primaryModel']->save()) {
                Yii::$app->response->error = 'Unable to create object!';
            } else {
                Yii::$app->response->task = 'status';
                Yii::$app->response->success = '<em>'. $p['primaryModel']->descriptor .'</em> was created successfully.';
                if (isset($p['relatedType'])) {
                    $primaryModelClass = $p['type']->primaryModel;
                    Yii::$app->response->trigger = [
                        ['refresh', '.model-'. $primaryModelClass::baseClassName()]
                    ];
                } else {
                    Yii::$app->response->redirect = $p['primaryModel']->getUrl('view');
                }
            }
        }
        if (!($p['form'] = $p['relatedType']->getForm($p['primaryModel'], ['relationSettings' => $relationSettings, 'subform' => $p['objectRelationName']]))) {
            throw new HttpException(403, "There is nothing to create for {$p['type']->title->getPlural(true)}");
        }
        $p['form']->ajax = true;
    }

    public function actionUpdateField()
    {
        Yii::$app->response->task = 'status';
        if (empty($_POST['attribute']) || empty($_POST['object']) || !($object = $this->params['object'] = Registry::getObject($_POST['object'], false)) || !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
            throw new HttpException(404, "Unknown object.");
        }
        $relatedObject = false;
        if (!empty($_POST['relatedObject']) && (!($relatedObject = $this->params['relatedObject'] = Registry::getObject($_POST['relatedObject'], false)) || !($relatedTypeItem = $this->params['typeItem'] = $relatedObject->objectTypeItem))) {
            throw new HttpException(404, "Unknown related object.");
        }
        if (!$object->can('update')) {
            throw new HttpException(403, "Unable to update object.");
        }
        if (in_array($_POST['attribute'], ['id', 'created', 'created_user_id', 'modified', 'modified_user_id', 'archived', 'archived_user_id'])) {
            throw new HttpException(403, "Invalid attribute!");
        }
        $object->attributes = [$_POST['attribute'] => $_POST['value']];
        if ($relatedObject) {
            $object->indirectObject = $relatedObject;
        }
        if ($object->save()) {
            Yii::$app->response->success = $object->descriptor .' was updated';
        } else {
            Yii::$app->response->error = 'Unable to update '. $object->descriptor;
        }
    }



    /**
     * __method_actionAccess_description__
     */
    public function actionAccess()
    {
        $subform = null;
        $p = $this->_parseParams();
        $this->_checkParams($p, ['object', 'type']);
        $this->params = &$p;

        $primaryModel = $p['type']->primaryModel;
        $this->params['errors'] = [];
        Yii::$app->response->view = 'access';
        $taskOptions = ['title' => 'Access for '. $p['type']->title->getSingular(true)];
        $lookAtPost = false;
        if ($p['object']->can('manageAccess')) {
            $lookAtPost = true;
            $taskOptions['title'] = 'Manage ' . $taskOptions['title'];
            $taskOptions['isForm'] = false;
        }
        $this->params['access'] = $access = $p['object']->objectAccess;
        $this->params['disableFields'] = !$lookAtPost;
        $taskOptions['isForm'] = $lookAtPost;
        $objectRoles = $access->roleObjects;
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = $taskOptions;
        if ($lookAtPost && !empty($_POST['roles'])) {
            $result = $access->save($_POST['roles']);
            if (!empty($result['errors'])) {
                if (is_array($result['errors'])) {
                    $this->params['errors'] = $result['errors'];
                    Yii::$app->response->error = 'An error occurred while saving the object\'s sharing settings.';
                } else {
                    Yii::$app->response->error = $result['errors'];
                }
                foreach ($result['data'] as $requestorId => $roleId) {
                    $objectRole = $access->getRoleObject($requestorId, $roleId);
                    if (!isset($objectRoles[$requestorId])
                            || (isset($objectRoles[$requestorId]['role'])
                                    && $objectRoles[$requestorId]['role']->object->primaryKey !== $roleId)
                    ) {
                        $objectRoles[$requestorId] = $objectRole;
                    }
                }
            } else {
                Yii::$app->response->task = 'status';
                Yii::$app->response->success = 'Access has been updated.';
                if (empty($p['relatedObject'])) {
                    Yii::$app->response->refresh = true;
                } else {
                    $primaryModel = $p['type']->primaryModel;
                    Yii::$app->response->trigger = [
                        ['refresh', '.model-'. $primaryModel::baseClassName()]
                    ];
                }
            }
        }
        $this->params['objectRoles'] = $objectRoles;
    }

    /**
     * __method_actionDelete_description__
     * @throws HttpException __exception_HttpException_description__
     */
    public function actionDelete()
    {
        $subform = null;
        $p = $this->_parseParams();
        $this->_checkParams($p, ['object', 'type']);
        $this->params = &$p;
        $primaryModel = $p['type']->primaryModel;

        $this->params['model'] = new DeleteForm;
        if (isset($p['relation'])) {
            $this->params['model']->object = $p['object'];
            $this->params['model']->relationship = $p['relationship'];
            $this->params['model']->relationModel = $p['relation'];
            $this->params['model']->relationshipWith = $p['relatedObject'];
            $this->params['model']->object->indirectObject = $p['relatedObject'];
            $primaryObject = $p['object'];
        } else {
            $primaryObject = $p['object'];
            $this->params['model']->object = $p['object'];
        }

        if (empty($this->params['model']->possibleTargets)) {
            throw new HttpException(403, "You are not authorized to perform this action.");
        }

        Yii::$app->response->view = 'delete';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => 'Delete '. $p['type']->title->getSingular(true) , 'isConfirmDeletion' => true];

        if (!empty($_POST['DeleteForm'])) {
            Yii::$app->response->task = 'status';
            $targetDescriptor = $this->params['model']->targetDescriptor;
            $this->params['model']->attributes = $_POST['DeleteForm'];
            if (!$this->params['model']->handle()) {
                Yii::$app->response->error =  'Could not '. $this->params['model']->targetLabel['long'];
            } else {
                Yii::$app->response->success = ucfirst($this->params['model']->targetLabel['past']). '.';
                if (isset($this->params['model']->targetLabel['response']) && empty($p['relation'])) {
                    switch ($this->params['model']->targetLabel['response']) {
                        case 'home':
                            Yii::$app->response->redirect = '/';
                        break;
                        case 'refresh':
                            Yii::$app->response->refresh = true;
                        break;
                    }
                } else {
                    Yii::$app->response->trigger = [
                        ['refresh', '.model-'. $p['object']::baseClassName()]
                    ];
                }
            }
        }
    }

    /**
     * __method_actionWatch_description__
     * @throws HttpException __exception_HttpException_description__
     */
    public function actionWatch()
    {
        if (empty($_GET['id']) or !($object = $this->params['object'] = Registry::getObject($_GET['id'], false)) or !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
            throw new HttpException(404, "Unknown object.");
        }
        if (!$object->can('read')) {
            throw new HttpException(403, "Unable to access object.");
        }
        Yii::$app->request->object = $object;

        $watching = empty($_GET['stop']);
        if ($object->watch($watching)) {
            Yii::$app->response->task = 'trigger';
            if ($watching) {
                Yii::$app->response->success = 'You are now watching '. $object->descriptor .'.';
                Yii::$app->response->trigger = [
                    ['startedWatching']
                ];
            } else {
                Yii::$app->response->success = 'You stopped watching '. $object->descriptor .'.';
                Yii::$app->response->trigger = [
                    ['stoppedWatching']
                ];
            }
        } else {
            Yii::$app->response->error = 'Unable update the watching status of this object.';
        }
    }


    /**
     * __method_actionWidget_description__
     */
    public function actionWidget()
    {
        $package = [];
        $renderWidgets = [];
        if (!empty($_POST['widgets'])) {
            $renderWidgets = $_POST['widgets'];
            $baseState = ['fetch' => 0];
        } elseif (!empty($_GET['widgets'])) {
            $renderWidgets = $_GET['widgets'];
            $baseState = ['fetch' => 1];
            ob_start();
            ob_implicit_flush(false);
        }
        $sectionCount = count($renderWidgets);
        if (isset($_GET['sectionCount'])) {
            $sectionCount = (int) $_GET['sectionCount'];
        }
        if (isset($_POST['sectionCount'])) {
            $sectionCount = (int) $_POST['sectionCount'];
        }
        if (!empty($renderWidgets)) {
            foreach ($renderWidgets as $i => $widget) {
                $w = [];
                if (empty($widget['state'])) { $widget['state'] = []; }
                if (empty($widget['data'])) { $widget['data'] = []; }
                if (!isset($widget['data']['sectionCount'])) {
                    $widget['data']['sectionCount'] = $sectionCount;
                }
                $w['rendered'] = Yii::$app->widgetEngine->build($widget['name'], $widget['data'], [], array_merge($baseState, $widget['state']));
                $w['id'] =  Yii::$app->widgetEngine->lastBuildId;
                $package[$i] = $w;
            }
        }
        //sleep(3);
        $this->params['widgets'] = $package;
        //var_dump($package);exit;
        $this->json();
    }
}
