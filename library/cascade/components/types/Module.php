<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use Yii;

use cascade\models\Group;
use cascade\models\Relation;
use cascade\models\Registry;
use cascade\components\web\ObjectViewEvent;

use infinite\base\exceptions\Exception;
use infinite\base\exceptions\HttpException;
use infinite\base\language\Noun;
use infinite\db\ActiveRecord;
use infinite\security\Access;

use yii\base\Controller;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     * @var __var__title_type__ __var__title_description__
     */
    protected $_title;
    /**
     * @var __var_version_type__ __var_version_description__
     */
    public $version = 1;

    /**
     * @var __var_objectSubInfo_type__ __var_objectSubInfo_description__
     */
    public $objectSubInfo = [];
    /**
     * @var __var_icon_type__ __var_icon_description__
     */
    public $icon = 'ic-icon-info';
    /**
     * @var __var_priority_type__ __var_priority_description__
     */
    public $priority = 1000; //lower is better

    /**
     * @var __var_hasDashboard_type__ __var_hasDashboard_description__
     */
    public $hasDashboard = true;
    /**
     * @var __var_uniparental_type__ __var_uniparental_description__
     */
    public $uniparental = false;

    /**
     * @var __var_searchWeight_type__ __var_searchWeight_description__
     */
    public $searchWeight = 1; // overall weight of item in search results
    /**
     * @var __var_childSearchWeight_type__ __var_childSearchWeight_description__
     */
    public $childSearchWeight = false; // weight when a child of a searchable object
    /**
     * @var __var_parentSearchWeight_type__ __var_parentSearchWeight_description__
     */
    public $parentSearchWeight = false; // weight when a parent of a searchable object

    /**
     * @var __var_sectionName_type__ __var_sectionName_description__
     */
    public $sectionName;

    /**
     * @var __var_widgetNamespace_type__ __var_widgetNamespace_description__
     */
    public $widgetNamespace;
    /**
     * @var __var_modelNamespace_type__ __var_modelNamespace_description__
     */
    public $modelNamespace;

    /**
     * @var __var_formGeneratorClass_type__ __var_formGeneratorClass_description__
     */
    public $formGeneratorClass = 'cascade\\components\\web\\form\\Generator';
    /**
     * @var __var_sectionItemClass_type__ __var_sectionItemClass_description__
     */
    public $sectionItemClass = 'cascade\\components\\section\\Item';
    /**
     * @var __var_sectionWidgetClass_type__ __var_sectionWidgetClass_description__
     */
    public $sectionWidgetClass = 'cascade\\components\\web\\widgets\\section\\Section';
    /**
     * @var __var_sectionSingleWidgetClass_type__ __var_sectionSingleWidgetClass_description__
     */
    public $sectionSingleWidgetClass = 'cascade\\components\\web\\widgets\\section\\SingleSection';
    /**
     * @var __var_fallbackDetailsWidgetClass_type__ __var_fallbackDetailsWidgetClass_description__
     */
    public $fallbackDetailsWidgetClass = 'cascade\\components\\web\\widgets\\base\\Details';

    /**
     * @var __var__objectTypeModel_type__ __var__objectTypeModel_description__
     */
    protected $_objectTypeModel;

    const EVENT_RELATION_CHANGE = 'onRelationChange';
    const EVENT_RELATION_DELETE = 'onRelationDelete';
    const EVENT_VIEW_OBJECT = 'onViewObject';

    /**
     * @var __var__disabledFields_type__ __var__disabledFields_description__
     */
    protected $_disabledFields;

    /**
    * @inheritdoc
     */
    public function init()
    {
        if (isset($this->modelNamespace)) {
            Yii::$app->registerModelAlias(':'. $this->systemId, $this->modelNamespace);
        }
        $this->on(self::EVENT_VIEW_OBJECT, [$this, 'subactionHandle']);
        parent::init();
    }

    /**
    * @inheritdoc
     */
    public function getCollectorName()
    {
        return 'types';
    }

    /**
     * __method_onBeforeControllerAction_description__
     * @param unknown       $controller
     * @param unknown       $action
     * @return unknown
     * @throws HttpException __exception_HttpException_description__
     */
    public function onBeforeControllerAction($controller, $action)
    {
        if (!isset($_SERVER['PASS_THRU']) or $_SERVER['PASS_THRU'] != md5(Yii::$app->params['salt'] . 'PASS')) {
            throw new HttpException(400, 'Invalid request!');
        }

        return parent::onBeforeControllerAction($event);
    }

    /**
    * @inheritdoc
     */
    public function onAfterInit($event)
    {
        if (!isset(Yii::$app->collectors['taxonomies']) || !Yii::$app->collectors['taxonomies']->registerMultiple($this, $this->taxonomies())) { throw new Exception('Could not register taxonmies for '. $this->systemId .'!'); }
        if (!isset(Yii::$app->collectors['widgets']) || !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) { throw new Exception('Could not register widgets for '. $this->systemId .'!'); }
        // if (!isset(Yii::$app->collectors['roles']) || !Yii::$app->collectors['roles']->registerMultiple($this, $this->roles())) { throw new Exception('Could not register roles for '. $this->systemId .'!'); }
        // if (!isset(Yii::$app->collectors['tools']) || !Yii::$app->collectors['tools']->registerMultiple($this, $this->tools())) { throw new Exception('Could not register tools for '. $this->systemId .'!'); }
        // if (!isset(Yii::$app->collectors['reports']) || !Yii::$app->collectors['reports']->registerMultiple($this, $this->reports())) { throw new Exception('Could not register reports for '. $this->systemId .'!'); }
        return parent::onAfterInit($event);
    }


    public function getPageMeta()
    {
        $m = [];
        $m['id'] = $this->systemId;
        $m['icon'] = $this->icon;
        $m['hasDashboard'] = $this->hasDashboard;
        $m['title'] = $this->getTitle()->package;
        return $m;
    }

    public function getRelatedType($name)
    {
        list($relationship, $role) = $this->getRelationship($name);
        if ($relationship) {
            return $relationship->roleType($role);
        }
        return false;
    }

    public function getRelationship($name)
    {
        $parts = explode(':', $name);
        $item = $this->collectorItem;
        if (count($parts) > 1) {
            if (in_array($parts[0], ['child', 'children', 'descendents'])) {
                if (isset($item->children[$parts[1]])) {
                    return [$item->children[$parts[1]], 'child'];
                }
            } else {
                if (isset($item->parents[$parts[1]])) {
                    return [$item->parents[$parts[1]], 'parent'];
                }
            }
        }
        return [false, false];
    }

    /**
     * __method_subactionHandle_description__
     * @param cascade\components\web\ObjectViewEvent $event __param_event_description__
     * @return __return_subactionHandle_type__        __return_subactionHandle_description__
     */
    public function subactionHandle(ObjectViewEvent $event)
    {
        $subactions = $this->subactions();
        if (isset($subactions[$event->action])) {
            if (is_callable($subactions[$event->action])) {
                $event->handleWith($subactions[$event->action]);

                return;
            } elseif (isset($subactions[$event->action]['callback'])) {
                $always = !empty($subactions[$event->action]['always']);
                $event->handleWith($subactions[$event->action]['callback'], $always);
            }
        }

        return;
    }

    /**
     * __method_subactions_description__
     * @return __return_subactions_type__ __return_subactions_description__
     */
    public function subactions()
    {
        return [];
    }

    /**
     * Set up
     * @return __return_setup_type__ __return_setup_description__
     */
    public function setup()
    {
        $results = [true];
        if (!empty($this->primaryModel) && !empty($this->collectorItem->parents)) {
            $groups = ['top'];
            foreach ($groups as $groupName) {
                $group = Group::getBySystemName($groupName, false);
                if (empty($group)) { continue; }
                if ($this->inheritParentAccess) {
                    $results[] = $this->objectTypeModel->parentAccess(null, $group);
                }
            }
        }

        return min($results);
    }

    /**
     * Get inherit parent access
     * @return __return_getInheritParentAccess_type__ __return_getInheritParentAccess_description__
     */
    public function getInheritParentAccess()
    {
        return !$this->hasDashboard;
    }

    /**
     * __method_determineOwner_description__
     * @param __param_object_type__          $object __param_object_description__
     * @return __return_determineOwner_type__ __return_determineOwner_description__
     */
    public function determineOwner($object)
    {
        if (isset(Yii::$app->user)
            && !Yii::$app->user->isGuest
            && isset(Yii::$app->user->identity)
            ){
            if (!empty(Yii::$app->user->identity->object_individual_id)) {
                return Yii::$app->user->identity->object_individual_id;
            } else {
                return false; //Yii::$app->user->id;
            }
        }

        return false;
    }

    /**
     * Get role help text
     * @param __param_roleItem_type__         $roleItem __param_roleItem_description__
     * @param __param_object_type__           $object   __param_object_description__ [optional]
     * @return __return_getRoleHelpText_type__ __return_getRoleHelpText_description__
     */
    public function getRoleHelpText($roleItem, $object = null)
    {
        switch ($roleItem->systemId) {
            case 'owner':
                return 'Able to manage access, delete, edit, and transfer ownership.';
            break;
            case 'manager':
                return 'Able to manage access, delete, and edit.';
            break;
            case 'editor':
                return 'Able to edit and archive.';
            break;
            case 'viewer':
                return 'Able to view content.';
            break;
            case 'browser':
                return 'Able to see name in lists, but cannot see content.';
            break;
        }

        return null;
    }

    /**
     * __method_determineAccessLevel_description__
     * @param __param_object_type__                $object __param_object_description__
     * @param __param_role_type__                  $role   __param_role_description__
     * @param __param_aro_type__                   $aro    __param_aro_description__ [optional]
     * @return __return_determineAccessLevel_type__ __return_determineAccessLevel_description__
     */
    public function determineAccessLevel($object, $role, $aro = null)
    {
        switch ($role) {
            case 'owner':
                return ['list' => Access::ACCESS_GRANTED, 'read' => Access::ACCESS_GRANTED, 'update' => Access::ACCESS_GRANTED, 'archive' => Access::ACCESS_GRANTED, 'delete' => Access::ACCESS_GRANTED, 'manageAccess' => Access::ACCESS_GRANTED, 'transfer' => Access::ACCESS_GRANTED];
            break;
            case 'manager':
                return ['list' => Access::ACCESS_GRANTED, 'read' => Access::ACCESS_GRANTED, 'update' => Access::ACCESS_GRANTED, 'archive' => Access::ACCESS_GRANTED, 'manageAccess' => Access::ACCESS_GRANTED];
            break;
            case 'editor':
                return ['list' => Access::ACCESS_GRANTED, 'read' => Access::ACCESS_GRANTED, 'update' => Access::ACCESS_GRANTED, 'archive' => Access::ACCESS_GRANTED];
            break;
            case 'viewer':
                return ['list' => Access::ACCESS_GRANTED, 'read' => Access::ACCESS_GRANTED];
            break;
            case 'browser':
                return ['list' => Access::ACCESS_GRANTED];
            break;
        }

        return false;
    }

    /**
     * Get action map
     * @param __param_controlledObject_type__ $controlledObject __param_controlledObject_description__ [optional]
     * @return __return_getActionMap_type__    __return_getActionMap_description__
     */
    public function getActionMap($controlledObject = null)
    {
        return [];
    }

    public function getPrimaryAsChild(Module $parent)
    {
        return false;
    }

    public function getPrimaryAsParent(Module $child)
    {
        return false;
    }

    /**
     * Get disabled fields
     * @return __return_getDisabledFields_type__ __return_getDisabledFields_description__
     */
    public function getDisabledFields()
    {
        if (is_null($this->_disabledFields)) {
            return [];
        }

        return $this->_disabledFields;
    }

    /**
     * Set disabled fields
     * @param __param_fields_type__ $fields __param_fields_description__
     */
    public function setDisabledFields($fields)
    {
        $this->_disabledFields = $fields;
    }

    /**
     * Get primary model
     * @return __return_getPrimaryModel_type__ __return_getPrimaryModel_description__
     */
    public function getPrimaryModel()
    {
        return $this->modelNamespace .'\\'. 'Object'.$this->systemId;
    }

    /**
    * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Type';
    }

    /**
     * __method_upgrade_description__
     * @param __param_from_type__     $from __param_from_description__
     * @return __return_upgrade_type__ __return_upgrade_description__
     */
    public function upgrade($from)
    {
        return $this->setup();
    }

    /**
     * Get creator role
     * @return __return_getCreatorRole_type__ __return_getCreatorRole_description__
     */
    public function getCreatorRole()
    {
        return [];
    }

    /**
     * Get role validation settings
     * @param __param_object_type__                     $object __param_object_description__ [optional]
     * @return __return_getRoleValidationSettings_type__ __return_getRoleValidationSettings_description__
     */
    public function getRoleValidationSettings($object = null)
    {
        $settings = [];
        $settings['possibleRoles'] = $this->possibleRoles;

        return $settings;
    }

    /**
     * Get possible roles
     * @return __return_getPossibleRoles_type__ __return_getPossibleRoles_description__
     */
    public function getPossibleRoles()
    {
        $roles = [];
        foreach (Yii::$app->collectors['roles']->getAll() as $roleItem) {
            $test = true;
            switch ($roleItem->systemId) {
                case 'owner':
                    $test = $this->isOwnable;
                break;
            }
            if ($test) {
                $roles[] = $roleItem->object->primaryKey;
            }
        }

        return $roles;
    }

    /**
     * Get required roles
     * @return __return_getRequiredRoles_type__ __return_getRequiredRoles_description__
     */
    public function getRequiredRoles()
    {
        $roles = [];
        foreach (Yii::$app->collectors['roles']->getAll() as $roleItem) {
            $test = false;
            switch ($roleItem->systemId) {
                case 'owner':
                    $test = $this->isOwnable;
                break;
            }
            if ($test) {
                $roles[] = $roleItem->object->primaryKey;
            }
        }

        return $roles;
    }

    /**
     * Get initial role
     * @return __return_getInitialRole_type__ __return_getInitialRole_description__
     */
    public function getInitialRole()
    {
        $roles = [];
        foreach (Yii::$app->collectors['roles']->getAll() as $roleItem) {
            $test = $roleItem->level < 400;
            if ($test) {
                $roles[] = $roleItem->object->primaryKey;
            }
        }

        return $roles;
    }

    /**
     * Get is ownable
     * @return __return_getIsOwnable_type__ __return_getIsOwnable_description__
     */
    public function getIsOwnable()
    {
        return $this->dummyModel->getBehavior('Ownable') !== null && $this->dummyModel->getBehavior('Ownable')->isEnabled();
    }

    /**
     * Get owner object
     * @return __return_getOwnerObject_type__ __return_getOwnerObject_description__
     */
    public function getOwnerObject()
    {
        return null;
    }

    /**
     * Get owner
     * @return __return_getOwner_type__ __return_getOwner_description__
     */
    public function getOwner()
    {
        if (!$this->isOwnable) {
            return null;
        }
        $ownerObject = $this->getOwnerObject();
        if (is_object($ownerObject)) {
            return $ownerObject->primaryKey;
        }

        return $ownerObject;
    }

    /**
     * Get object type model
     * @return __return_getObjectTypeModel_type__ __return_getObjectTypeModel_description__
     */
    public function getObjectTypeModel()
    {
        if (!isset($this->_objectTypeModel) && isset(Yii::$app->collectors['types']->tableRegistry[$this->systemId])) {
            $this->_objectTypeModel = Yii::$app->collectors['types']->tableRegistry[$this->systemId];
        }

        return $this->_objectTypeModel;
    }

    /**
     * Set object type model
     * @param __param_model_type__ $model __param_model_description__
     */
    public function setObjectTypeModel($model)
    {
        $this->_objectTypeModel = $model;
    }
    /**
     * __method_search_description__
     * @param unknown $term
     * @param array   $params __param_params_description__ [optional]
     * @return unknown
     */
    public function search($term, $params = [])
    {
        if (!$this->primaryModel) { return false; }

        $results = [];
        $modelClass = $this->primaryModel;

        return $modelClass::searchTerm($term, $params);
    }

    /**
     * Get object level
     * @return __return_getObjectLevel_type__ __return_getObjectLevel_description__
     */
    public function getObjectLevel()
    {
        if ($this->isPrimaryType) {
            return 1;
        }
        $parents = $this->collectorItem->parents;
        if (!empty($parents)) {
            $maxLevel = 1;
            foreach ($parents as $rel) {
                if (get_class($rel->parent) === get_class($this)) { continue; }
                $newLevel = $rel->parent->objectLevel + 1;
                if ($newLevel > $maxLevel) {
                    $maxLevel = $newLevel;
                }
            }

            return $maxLevel;
        }

        return 1;
    }
    /**
     * Get section
     * @param __param_parentWidget_type__ $parentWidget __param_parentWidget_description__ [optional]
     * @param unknown                     $settings     (optional)
     * @return unknown
     */
    public function getSection($parentWidget = null, $settings = [])
    {
        $name = $this->systemId;
        $parent = false;
        $child = false;
        if (isset($settings['relationship']) && isset($settings['queryRole'])) {
            if ($settings['relationship']->companionRole($settings['queryRole']) === 'parent') {
                $parent = $settings['relationship']->parent;
            } else {
                $child = $settings['relationship']->child;
            }
        }
        if (($parent && $parent->systemId === $this->systemId) || ($child && $child->systemId === $this->systemId)) {
            $sectionId = $settings['relationship']->systemId.'-'.$this->systemId;
            $section = Yii::$app->collectors['sections']->getOne($sectionId);
            $section->title = '%%relationship%% %%type.'. $this->systemId .'.title.upperPlural%%';
            $section->icon = $this->icon;
            $section->systemId = $sectionId;
            if (empty($section->object)) {
                $sectionConfig = ['class' => $this->sectionSingleWidgetClass, 'section' => $section];
                $section->priority = $this->priority + 10000;
                $section->object = Yii::createObject($sectionConfig);
            }

            return $section;
        }
        $sectionClass = $this->sectionSingleWidgetClass;
        $sectionItemClass = $this->sectionItemClass;
        $newSectionTitle = '%%type.'. $this->systemId .'.title.upperPlural%%';
        $sectionId = $this->systemId;
        if (!is_null($this->sectionName)) {
            $sectionId = $sectionItemClass::generateSectionId($this->sectionName);
            if (Yii::$app->collectors['sections']->has($sectionId)) {
                return Yii::$app->collectors['sections']->getOne($sectionId);
            }
            $newSectionTitle = $this->sectionName;
            $sectionClass = $this->sectionWidgetClass;
        }
        $section = Yii::$app->collectors['sections']->getOne($sectionId);
        if (empty($section->object)) {
            $section->title = $newSectionTitle;
            $section->priority = $this->priority;
            $section->icon = $this->icon;
            $sectionConfig = ['class' => $sectionClass, 'section' => $section];
            $section->object = Yii::createObject($sectionConfig);
        }

        return $section;
    }

    /**
     * Get title
     * @return unknown
     */
    public function getTitle()
    {
        if (!is_object($this->_title)) {
            $this->_title = new Noun($this->_title);
        }

        return $this->_title;
    }

    /**
     * Set title
     * @param __param_title_type__ $title __param_title_description__
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * Get details widget
     * @param __param_objectModel_type__       $objectModel __param_objectModel_description__ [optional]
     * @return __return_getDetailsWidget_type__ __return_getDetailsWidget_description__
     */
    public function getDetailsWidget($objectModel = null)
    {
        if (is_null($objectModel) && isset(Yii::$app->request->object)) {
            $objectModel = Yii::$app->request->object;
        } elseif (is_null($objectModel)) {
            $objectModel = $this->dummyModel;
        }

        $detailsSection = $this->getDetailsSection();
        if ($detailsSection === false) { return false; }
        if ($detailsSection === true) {
            $detailsSection = '_self';
        }

        $detailsWidgetClass = self::classNamespace() .'\widgets\\'. 'Details';
        $widgetClass = $this->fallbackDetailsWidgetClass;

        @class_exists($detailsWidgetClass);
        if (class_exists($detailsWidgetClass, false)) {
            $widgetClass = $detailsWidgetClass;
        }
        $widget = ['class' => $widgetClass];
        $widget['owner'] = $this;
        $widgetItem = ['widget' => $widget, 'locations' => ['self'], 'priority' => 1];
        $widgetItem['section'] = Yii::$app->collectors['sections']->getOne($detailsSection);

        return $widgetItem;
    }

    /**
     * Get details section
     * @return __return_getDetailsSection_type__ __return_getDetailsSection_description__
     */
    public function getDetailsSection()
    {
        return '_side';
    }

    /**
     * __method_widgets_description__
     * @return __return_widgets_type__ __return_widgets_description__
     */
    public function widgets()
    {
        $widgets = [];

        $detailsWidget = $this->getDetailsWidget();
        if ($detailsWidget) {
            $id = '_'. $this->systemId .'Details';
            $widgets[$id] = $detailsWidget;
        }

        $detailListClassName = self::classNamespace() .'\widgets\\'. 'DetailList';
        $simpleListClassName = self::classNamespace() .'\widgets\\'. 'SimpleLinkList';
        @class_exists($detailListClassName);
        @class_exists($simpleListClassName);

        $baseWidget = [];
        if ($this->module instanceof \cascade\components\section\Module) {
            $baseWidget['section'] = $this->module->collectorItem;
        }

        if (!$this->isChildless) {
            if (!class_exists($detailListClassName, false)) { $detailListClassName = false; }
            if (!class_exists($simpleListClassName, false)) { $simpleListClassName = false; }
            // needs widget for children and summary page
            if ($detailListClassName) {
                $childrenWidget = $baseWidget;
                $id = 'Parent'. $this->systemId .'Browse';
                $childrenWidget['widget'] = [
                    'class' => $detailListClassName,
                    'icon' => $this->icon,
                    'title' => '%%relationship%% %%type.'. $this->systemId .'.title.upperPlural%%'
                ];
                $childrenWidget['locations'] = ['child_objects'];
                $childrenWidget['priority'] = $this->priority;
                $childrenWidget['section'] = Yii::$app->collectors['sections']->getOne('_parents');
                $widgets[$id] = $childrenWidget;
            } else {
                Yii::trace("Warning: There is no browse class for the child objects of {$this->systemId}");
            }
            if ($this->hasDashboard && $simpleListClassName) {
                $summaryWidget = $baseWidget;
                $id = $this->systemId .'Summary';
                $summaryWidget['widget'] = [
                    'class' => $simpleListClassName,
                    'icon' => $this->icon,
                    'title' => '%%type.'. $this->systemId .'.title.upperPlural%%'
                ];
                $summaryWidget['locations'] = ['front'];
                $summaryWidget['priority'] = $this->priority;
                $widgets[$id] = $summaryWidget;
            } else {
                Yii::trace("Warning: There is no summary class for {$this->systemId}");
            }
        } else {
            if (!class_exists($detailListClassName, false)) { $detailListClassName = false; }
            // needs widget for parents
        }
        if ($detailListClassName) {
            $parentsWidget = $baseWidget;
            $id = 'Children'. $this->systemId .'Browse';
            $parentsWidget['widget'] = [
                    'class' => $detailListClassName,
                    'icon' => $this->icon,
                    'title' => '%%relationship%% %%type.'. $this->systemId .'.title.upperPlural%%'
                ];
            $parentsWidget['locations'] = ['parent_objects'];
            $parentsWidget['priority'] = $this->priority + 1;
            $widgets[$id] = $parentsWidget;
        } else {
            Yii::trace("Warning: There is no browse class for the parent objects of {$this->systemId}");
        }
        return $widgets;
    }

    /**
     * __method_loadFieldLink_description__
     * @param __param_field_type__  $field     __param_field_description__
     * @param __param_object_type__ $object    __param_object_description__
     * @param boolean               $typeMatch __param_typeMatch_description__ [optional]
     */
    public function loadFieldLink($field, $object, $typeMatch = true)
    {
        if ($this->hasDashboard) {
            $field->url = ['/object/view', $object->id];
            if (!$typeMatch) {
                // what is being displayed isn't the same type as what is being linked to. put helper title.
                //		example: linking to an Individual from one of their phone numbers
                $field->linkOptions['title'] = $object->descriptor;
            }
        }
    }

    /**
     * __method_taxonomies_description__
     * @return unknown
     */
    public function taxonomies()
    {
        return [];
    }

    /**
     * __method_roles_description__
     * @return unknown
     */
    public function roles()
    {
        return [];
    }

    /**
     * __method_dependencies_description__
     * @return unknown
     */
    public function dependencies()
    {
        return [];
    }


    /**
     * __method_parents_description__
     * @return unknown
     */
    public function parents()
    {
        return [];
    }


    /**
     * Settings for
     * @return unknown
     */
    public function parentSettings()
    {
        return [
            'title' => false,
            'allow' => 1, // 0/false = no; 1 = only 1; 2 = 1 or more
            'showDescriptor' => false
        ];
    }


    /**
     * __method_childrenSettings_description__
     * @return unknown
     */
    public function childrenSettings()
    {
        return [
            'allow' => 2,  // 0/false = no; 1 = only 1; 2 = 1 or more
        ];
    }

    /**
     * __method_children_description__
     * @return unknown
     */
    public function children()
    {
        return [];
    }



    /**
     * Get dummy model
     * @return __return_getDummyModel_type__ __return_getDummyModel_description__
     */
    public function getDummyModel()
    {
        if (!$this->primaryModel) { return false; }
        $model = $this->primaryModel;

        return new $model;
    }

    /**
     * Get is childless
     * @return __return_getIsChildless_type__ __return_getIsChildless_description__
     */
    public function getIsChildless()
    {
        if (empty($this->collectorItem) || empty($this->collectorItem->children)) {
            return true;
        }

        return false;
    }

    /**
     * Get model
     * @param __param_primaryModel_type__ $primaryModel __param_primaryModel_description__ [optional]
     * @param array                       $input        __param_input_description__ [optional]
     * @return unknown
     */
    public function getModel($primaryModel = null, $input = false)
    {
        if (is_null($primaryModel)) {
            $primaryModel = $this->primaryModel;
            if (isset($input['id'])) {
                $primaryModel = $primaryModel::get($input['id']);
                if (empty($primaryModel)) {
                    return false;
                }
            } else {
                $primaryModel = new $primaryModel;
            }
        }
        $primaryModel->tabularId = false;

        if ($input && $input['_moduleHandler']) {
            $moduleHandler = $input['_moduleHandler'];
            $primaryModel->_moduleHandler = $moduleHandler;
            unset($input['_moduleHandler']);
            $primaryModel->setAttributes($input);
        } else {
            $primaryModel->loadDefaultValues();
        }

        return $primaryModel;
    }


    /**
     * Get form
     * @param __param_models_type__ $models   __param_models_description__ [optional]
     * @param array                 $settings __param_settings_description__ [optional]
     * @return unknown
     */
    public function getForm($primaryModel = false, $settings = [])
    {
        if (!$primaryModel) { return false; }
        $formSegments = [$this->getFormSegment($primaryModel, $settings)];
        $config = ['class' => $this->formGeneratorClass, 'models' => $primaryModel->collectModels(), 'items' => $formSegments];

        return Yii::createObject($config);
    }

    /**
     * Get form segment
     * @param __param_primaryModel_type__    $primaryModel __param_primaryModel_description__ [optional]
     * @param array                          $settings     __param_settings_description__ [optional]
     * @return __return_getFormSegment_type__ __return_getFormSegment_description__
     */
    public function getFormSegment($primaryModel = null, $settings = [])
    {
        if (empty($primaryModel)) {
            return false;
        }
        return $primaryModel->form($settings);
    }
}
