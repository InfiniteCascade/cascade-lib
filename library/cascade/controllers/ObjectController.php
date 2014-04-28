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
                    'browse' => ['get'],
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

        if ($viewEvent->handled) {
            if ($viewEvent->accessed) {
                ObjectFamiliarity::accessed($object);
            }

            return;
        }

        if (!$type->hasDashboard) {
            throw new HttpException(400, "Bad request");
        }

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
     * __method__parseParams_description__
     * @param array              $required __param_required_description__ [optional]
     * @param __param_can_type__ $can      __param_can_description__ [optional]
     * @param boolean            $swap     __param_swap_description__ [optional]
     * @throws HttpException __exception_HttpException_description__
     */
    protected function _parseParams($required = [], $can = null, $swap = false)
    {
        if (!empty($_GET['id']) && (!($this->params['object'] = Registry::getObject($_GET['id'], false)) || !($this->params['typeItem'] = $this->params['object']->objectTypeItem))) {
            throw new HttpException(404, "Unknown object.");
        }

        if (isset($this->params['object']) && (!($this->params['typeItem'] = $this->params['object']->objectTypeItem) || !($this->params['type'] = $this->params['typeItem']->object))) {
            throw new HttpException(404, "Unknown object type.");
        }

        if (isset($this->params['object'])) {
            $this->params['activeObject'] = $this->params['object'];
        }

        if (isset($this->params['type'])) {
            $this->params['activeType'] = $this->params['type'];
        }

        if (isset($this->params['typeItem'])) {
            $this->params['activeTypeItem'] = $this->params['typeItem'];
        }

        if (isset($_GET['related_object_id']) && isset($_GET['object_relation']) && isset($_GET['relationship_id'])) {
            $this->params['relationship'] = Relationship::getById($_GET['relationship_id']);
            if (!$this->params['relationship']) {
                throw new HttpException(404, "Unknown relationship type.");
            }
            $this->params['relatedModel'] = Registry::getObject($_GET['related_object_id'], true);
            if (isset($this->params['relatedModel']) && (!($this->params['relatedTypeItem'] = $this->params['relatedModel']->objectTypeItem) || !($this->params['relatedType'] = $this->params['relatedTypeItem']->object))) {
                throw new HttpException(404, "Unknown related object type.");
            }

            if ($_GET['object_relation'] === 'child') {
                $this->params['relationRole'] = 'child';
                $this->params['relation'] = $this->params['relationship']->getModel($_GET['related_object_id'], $this->params['object']->primaryKey);
            } else {
                $this->params['relationRole'] = 'parent';
                $this->params['relation'] = $this->params['relationship']->getModel($this->params['object']->primaryKey, $_GET['related_object_id']);
            }
            if (empty($this->params['relation'])) {
                throw new HttpException(404, "Unknown relationship.");
            }

        }

        if (!$this->params['type']->hasDashboard) { // || $this->params['type']->uniparental) {
            $required[] = 'relation';
        }

        if (!empty($_GET['link']) && !($this->params['relation'] = Relation::get($_GET['relation_id']))) {
            throw new HttpException(404, "Unknown relationship.");
        }

        if (!empty($_GET['relation_id']) && !($this->params['relation'] = Relation::get($_GET['relation_id']))) {
            throw new HttpException(404, "Unknown relationship.");
        }

        if (isset($this->params['relation']) && isset($this->params['object'])) {
            if (!isset($_GET['object_relation']) || !in_array($_GET['object_relation'], ['child', 'parent'])) {
                throw new HttpException(400, "Invalid request object relation");
            }
            $this->params['relatedObject'] = $this->params['object'];
            $can = 'update';
            $this->params['object'] = null;
            if ($_GET['object_relation'] === 'child') {
                $this->params['modelBucket'] = 'children';
                $this->params['object'] = Registry::getObject($this->params['relation']->parent_object_id);
            } else {
                $this->params['modelBucket'] = 'parents';
                $this->params['object'] = Registry::getObject($this->params['relation']->child_object_id);
            }
            if (!$this->params['object']) {
                throw new HttpException(404, "Unknown object.");
            }
            $this->params['object']->tabularId = ActiveRecord::getPrimaryTabularId();
            $this->params['object']->_moduleHandler = ActiveRecord::FORM_PRIMARY_MODEL;

            $this->params['subform'] = $_GET['object_relation'] . ':'. $this->params['relatedObject']->objectType->systemId;
            $this->params['relatedObject']->tabularId = $this->params['relatedObject']->id;
            $this->params['relation']->tabularId = $this->params['relatedObject']->id;
            $this->params['relatedObject']->_moduleHandler = $this->params['subform'];
            $this->params['relatedObject']->registerRelationModel($this->params['relation']);
            if (isset($_POST['Relation'][$this->params['relation']->tabularId])) {
                $this->params['relation']->attributes = $_POST['Relation'][$this->params['relation']->tabularId];
            }
        }

        if (!isset($this->params['handler']) && isset($this->params['object'])) {
            if (isset($this->params['relatedType'])) {
                $this->params['handler'] = $this->params['relatedType'];
            } else {
                $this->params['handler'] = $this->params['type'];
            }
        }

        if (!is_null($can) && isset($this->params['object'])) {
            if (isset($this->params['relatedObject'])) {
                $test = $this->params['relatedObject']->can($can, null, $this->params['object']);
            } else {
                $test = $this->params['object']->can($can);
            }

            if (!$test) {
                throw new HttpException(403, "You are not authorized to perform this action.");
            }
        }

        if (isset($_GET['subaction'])) {
            $this->params['subaction'] = $_GET['subaction'];
        }

        foreach ($required as $r) {
            if (!isset($this->params[$r])) {
                throw new HttpException(400, "Invalid request ({$r} is required)");
            }
        }
        if ($swap) {
            Yii::$app->request->object = $this->params['activeObject'];
        } else {
            Yii::$app->request->object = $this->params['object'];
        }
    }


    /**
     * __method_actionUpdate_description__
     * @return __return_actionUpdate_type__ __return_actionUpdate_description__
     * @throws HttpException __exception_HttpException_description__
     */
    public function actionUpdate()
    {
        $subform = null;
        $this->_parseParams(['object', 'type'], 'update');
        extract($this->params);
        $primaryModel = $type->primaryModel;
        if (isset($subaction) && $subaction === 'setPrimary') {
            if (!isset($relation)) {
                throw new HttpException(404, "Invalid relationship!");
            }
            Yii::$app->response->task = 'status';
            if ($relation->setPrimary($relationRole)) {
                Yii::$app->response->trigger = [
                    ['refresh', '.model-'. $primaryModel::baseClassName()]
                ];
            } else {
                Yii::$app->response->error = 'Unable to set primary object!';
            }

            return;
        } else {
            $refreshPrimary = isset($relatedObject);
            $updateRelationship = false;
            if (isset($relation)) {
                $updateRelationship = $relation;
            }
            $createPackage = [
                'object' => $object,
                'editObject' => $activeObject,
                'action' => 'update',
                'refreshPrimary' => $refreshPrimary,
                'updateRelationship' => $updateRelationship,
                'relationship' => isset($relationship) ? $relationship : null,
                'relationRole' => isset($relationRole) ? $relationRole : null
            ];
            $this->actionCreate($createPackage);
        }
    }

    /**
     * __method_actionCreate_description__
     * @throws HttpException __exception_HttpException_description__
     */
    public function actionCreate($createPackage = [])
    {
        $object = $editObject = null;
        $action = 'create';
        $refreshPrimary = false;
        $updateRelationship = false;
        extract($createPackage);
        if (!isset($_GET['type'])) { $_GET['type'] = ''; }
        $typeParsed = $originalTypeParsed= $_GET['type'];
        $typeParsedParts = explode(':', $typeParsed);
        $subform = null;
        $action = 'create';
        $relations = [];
        $reverseRelation = true;
        $forceNewRelation = false;
        $linkExisting = (!empty($_GET['link']) || $updateRelationship) ? 'hierarchy' : false;

        if (!isset($object)) {
            if (!empty($_GET['object_id']) && (!($object = $this->params['object'] = Registry::getObject($_GET['object_id'], false)) || !($typeItem = $this->params['typeItem'] = $object->objectTypeItem))) {
                throw new HttpException(404, "Unknown object.");
            }
        }
        $objectOriginal = $object;

        if ($updateRelationship) {
            $editObject = $object;
            $reverseRelation = false;
        } elseif ($linkExisting) {
            $editObject = $object;
            $reverseRelation = false;
            $forceNewRelation = true;
        }
        $checkType = true;
        if ($editObject) {
            $type = $editObject->objectType;
        }
        if (isset($typeParsedParts[1])) {
            $typeName = $typeParsedParts[1];
        } else {
            $typeName = $typeParsedParts[0];
            $checkType = false;
        }

        if (empty($type) && (empty($typeName) || !($type = Yii::$app->collectors['types']->getOne($typeName)) || !isset($type->object))) {
            throw new HttpException(404, "Unknown object type ". $typeName);
        }

        if ($editObject) {
            $type = $editObject->objectTypeItem;
            $module = $editObject->objectType;
            if (!$editObject->can('update')) {
                throw new HttpException(403, "You do not have access to update {$module->title->getPlural(true)}");
            }
        } else {
            $module = $type->object;
            if (!Yii::$app->gk->canGeneral('create', $module->primaryModel)) {
                throw new HttpException(403, "You do not have access to create {$module->title->getPlural(true)}");
            }
        }
        $primaryModel = $module->getModel($editObject);
        if (isset($object)) {
            if (!$object->can('update')) {
                throw new HttpException(403, "Unable to update object.");
            }
            $refreshPrimary = true;
            $fields = $primaryModel->getFields();
            if (empty($updateRelationship)) {
                if ($checkType) {
                    if (count($typeParsedParts) >= 2 && in_array($typeParsedParts[0], ['parent', 'child'])) {
                        list($relationship, $relationshipRole) = $object->objectType->getRelationship($typeParsed);
                        if ($relationship) {
                            if ($reverseRelation) {
                                $niceField = $relationship->getCompanionNiceId($relationshipRole);
                                $relationField = $relationship->companionRole($relationshipRole) .'_object_id';
                            } else {
                                $relationField = $relationship->companionRole($relationshipRole) .'_object_id';
                                $niceField = $relationship->getNiceId($relationshipRole);
                            }
                            $primaryModelClass = $relationship->roleType($relationshipRole)->primaryModel;
                            if ($forceNewRelation) {
                                $fields[$niceField]->resetModel();
                            }
                            $fields[$niceField]->model->{$relationField} = $object->primaryKey;
                            $relations[$fields[$niceField]->model->tabularId] = $fields[$niceField]->model;
                        }
                        $typeParsed = $typeParsedParts[1];
                        if ($linkExisting) {
                            $subform = implode(':', array_slice($typeParsedParts, 0, 2));
                        }
                    } else {
                        throw new HttpException(403, "Invalid request ");
                    }
                }
            } else {
                $niceField = $relationship->getNiceId($relationRole);
                $fields[$niceField]->model = $updateRelationship;
                $subform = $niceField;
            }
        }

        if ($linkExisting) {
            $action = 'link';
            $editObject = $objectOriginal;
        }


        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => ucfirst($action) . ' '.$module->title->getSingular(true) , 'width' => '800px'];

        $primaryModel->setRelationModels($relations);
        if (!empty($_POST)) {
            $primaryModel->load($_POST);
            if (!$primaryModel->save()) {
                Yii::$app->response->error = 'Unable to '. $action .' object!';
            } else {
                Yii::$app->response->task = 'status';
                Yii::$app->response->success = '<em>'. $primaryModel->descriptor .'</em> was saved successfully.';
                if ($refreshPrimary) {
                    if (!isset($primaryModelClass)) {
                        $primaryModelClass = $type->object->primaryModel;
                    }
                    Yii::$app->response->trigger = [
                        ['refresh', '.model-'. $primaryModelClass::baseClassName()]
                    ];
                } else {
                    Yii::$app->response->redirect = $primaryModel->getUrl('view');
                }
            }
        }
        if (!($this->params['form'] = $module->getForm($primaryModel, ['linkExisting' => $linkExisting, 'subform' => $subform]))) {
            throw new HttpException(403, "There is nothing to {$action} for {$module->title->getPlural(true)}");
        }
        $this->params['form']->ajax = true;
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
        $this->_parseParams(['activeObject', 'activeType'], 'read');
        extract($this->params);
        $primaryModel = $activeType->primaryModel;
        $this->params['errors'] = [];
        Yii::$app->response->view = 'access';
        $taskOptions = ['title' => 'Access for '. $activeType->title->getSingular(true)];
        $lookAtPost = false;
        if ($activeObject->can('manageAccess')) {
            $lookAtPost = true;
            $taskOptions['title'] = 'Manage ' . $taskOptions['title'];
            $taskOptions['isForm'] = false;
        }
        $this->params['access'] = $access = $activeObject->objectAccess;
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
                if (empty($relatedObject)) {
                    Yii::$app->response->refresh = true;
                } else {
                    $primaryModel = $relatedType->primaryModel;
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
        $this->_parseParams(['object', 'type']);
        extract($this->params);
        if (isset($relatedType)) {
            $primaryModel = $relatedType->primaryModel;
        } else {
            $primaryModel = $type->primaryModel;
        }

        $this->params['model'] = new DeleteForm;
        if (isset($relation)) {
            $this->params['model']->object = $relatedObject;
            $this->params['model']->relationship = $relationship;
            $this->params['model']->relationModel = $relation;
            $this->params['model']->relationshipWith = $object;
            $this->params['model']->object->indirectObject = $object;
            $primaryObject = $relatedObject;
        } else {
            $primaryObject = $object;
            $this->params['model']->object = $object;
        }

        if (empty($this->params['model']->possibleTargets)) {
            throw new HttpException(403, "You are not authorized to perform this action.");
        }

        Yii::$app->response->view = 'delete';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => 'Delete '. $type->title->getSingular(true) , 'isConfirmDeletion' => true];

        if (!empty($_POST['DeleteForm'])) {
            Yii::$app->response->task = 'status';
            $targetDescriptor = $this->params['model']->targetDescriptor;
            $this->params['model']->attributes = $_POST['DeleteForm'];
            if (!$this->params['model']->handle()) {
                Yii::$app->response->error =  'Could not '. $this->params['model']->targetLabel['long'];
            } else {
                Yii::$app->response->success = ucfirst($this->params['model']->targetLabel['past']). '.';
                if (isset($this->params['model']->targetLabel['response']) && empty($relation)) {
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
                        ['refresh', '.model-'. $primaryObject::baseClassName()]
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
