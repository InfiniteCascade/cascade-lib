<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use cascade\components\web\ObjectViewEvent;
use cascade\models\Group;
use infinite\base\exceptions\Exception;
use infinite\base\exceptions\HttpException;
use infinite\base\language\Noun;
use infinite\security\Access;
use Yii;
use yii\base\Controller;

/**
 * Module [[@doctodo class_description:cascade\components\types\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     * @var [[@doctodo var_type:_title]] [[@doctodo var_description:_title]]
     */
    protected $_title;
    /**
     * @var [[@doctodo var_type:version]] [[@doctodo var_description:version]]
     */
    public $version = 1;

    /**
     * @var [[@doctodo var_type:objectSubInfo]] [[@doctodo var_description:objectSubInfo]]
     */
    public $objectSubInfo = [];
    /**
     * @var [[@doctodo var_type:icon]] [[@doctodo var_description:icon]]
     */
    public $icon = 'ic-icon-info';
    /**
     * @var [[@doctodo var_type:priority]] [[@doctodo var_description:priority]]
     */
    public $priority = 1000; //lower is better

    /**
     * @var [[@doctodo var_type:hasDashboard]] [[@doctodo var_description:hasDashboard]]
     */
    public $hasDashboard = true;
    /**
     * @var [[@doctodo var_type:uniparental]] [[@doctodo var_description:uniparental]]
     */
    public $uniparental = false;

    /**
     * @var [[@doctodo var_type:searchWeight]] [[@doctodo var_description:searchWeight]]
     */
    public $searchWeight = 1; // overall weight of item in search results
    /**
     * @var [[@doctodo var_type:childSearchWeight]] [[@doctodo var_description:childSearchWeight]]
     */
    public $childSearchWeight = false; // weight when a child of a searchable object
    /**
     * @var [[@doctodo var_type:parentSearchWeight]] [[@doctodo var_description:parentSearchWeight]]
     */
    public $parentSearchWeight = false; // weight when a parent of a searchable object

    /**
     * @var [[@doctodo var_type:enableApiAccess]] [[@doctodo var_description:enableApiAccess]]
     */
    public $enableApiAccess = true;

    /**
     * @var [[@doctodo var_type:sectionName]] [[@doctodo var_description:sectionName]]
     */
    public $sectionName;

    /**
     * @var [[@doctodo var_type:widgetNamespace]] [[@doctodo var_description:widgetNamespace]]
     */
    public $widgetNamespace;
    /**
     * @var [[@doctodo var_type:modelNamespace]] [[@doctodo var_description:modelNamespace]]
     */
    public $modelNamespace;

    /**
     * @var [[@doctodo var_type:formGeneratorClass]] [[@doctodo var_description:formGeneratorClass]]
     */
    public $formGeneratorClass = 'cascade\components\web\form\Generator';
    /**
     * @var [[@doctodo var_type:sectionItemClass]] [[@doctodo var_description:sectionItemClass]]
     */
    public $sectionItemClass = 'cascade\components\section\Item';
    /**
     * @var [[@doctodo var_type:sectionWidgetClass]] [[@doctodo var_description:sectionWidgetClass]]
     */
    public $sectionWidgetClass = 'cascade\components\web\widgets\section\Section';
    /**
     * @var [[@doctodo var_type:sectionSingleWidgetClass]] [[@doctodo var_description:sectionSingleWidgetClass]]
     */
    public $sectionSingleWidgetClass = 'cascade\components\web\widgets\section\SingleSection';
    /**
     * @var [[@doctodo var_type:fallbackDetailsWidgetClass]] [[@doctodo var_description:fallbackDetailsWidgetClass]]
     */
    public $fallbackDetailsWidgetClass = 'cascade\components\web\widgets\base\Details';

    /**
     * @var [[@doctodo var_type:_objectTypeModel]] [[@doctodo var_description:_objectTypeModel]]
     */
    protected $_objectTypeModel;

    const EVENT_RELATION_CHANGE = 'onRelationChange';
    const EVENT_RELATION_DELETE = 'onRelationDelete';
    const EVENT_VIEW_OBJECT = 'onViewObject';

    /**
     * @var [[@doctodo var_type:_disabledFields]] [[@doctodo var_description:_disabledFields]]
     */
    protected $_disabledFields;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->always();
        $this->on(self::EVENT_VIEW_OBJECT, [$this, 'subactionHandle']);
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function always()
    {
        parent::always();
        if (isset($this->modelNamespace)) {
            Yii::$app->registerModelAlias(':' . $this->systemId, $this->modelNamespace);
        }
    }

    /**
     * @inheritdoc
     */
    public function getCollectorName()
    {
        return 'types';
    }

    /**
     * [[@doctodo method_description:onBeforeControllerAction]].
     *
     * @param unknown $controller
     * @param unknown $action
     *
     * @throws HttpException [[@doctodo exception_description:HttpException]]
     * @return unknown
     *
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
        if (!isset(Yii::$app->collectors['taxonomies']) || !Yii::$app->collectors['taxonomies']->registerMultiple($this, $this->taxonomies())) {
            throw new Exception('Could not register taxonmies for ' . $this->systemId . '!');
        }
        if (!isset(Yii::$app->collectors['widgets']) || !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) {
            throw new Exception('Could not register widgets for ' . $this->systemId . '!');
        }
        // if (!isset(Yii::$app->collectors['roles']) || !Yii::$app->collectors['roles']->registerMultiple($this, $this->roles())) { throw new Exception('Could not register roles for '. $this->systemId .'!'); }
        // if (!isset(Yii::$app->collectors['tools']) || !Yii::$app->collectors['tools']->registerMultiple($this, $this->tools())) { throw new Exception('Could not register tools for '. $this->systemId .'!'); }
        // if (!isset(Yii::$app->collectors['reports']) || !Yii::$app->collectors['reports']->registerMultiple($this, $this->reports())) { throw new Exception('Could not register reports for '. $this->systemId .'!'); }
        return parent::onAfterInit($event);
    }

    /**
     * Get page meta.
     *
     * @return [[@doctodo return_type:getPageMeta]] [[@doctodo return_description:getPageMeta]]
     */
    public function getPageMeta()
    {
        $m = [];
        $m['id'] = $this->systemId;
        $m['icon'] = $this->icon;
        $m['hasDashboard'] = $this->hasDashboard;
        $m['title'] = $this->getTitle()->package;

        return $m;
    }

    /**
     * Get related type.
     *
     * @return [[@doctodo return_type:getRelatedType]] [[@doctodo return_description:getRelatedType]]
     */
    public function getRelatedType($name)
    {
        list($relationship, $role) = $this->getRelationship($name);
        if ($relationship) {
            return $relationship->roleType($role);
        }

        return false;
    }

    /**
     * Get relationship.
     *
     * @return [[@doctodo return_type:getRelationship]] [[@doctodo return_description:getRelationship]]
     */
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
     * [[@doctodo method_description:subactionHandle]].
     *
     * @param cascade\components\web\ObjectViewEvent $event [[@doctodo param_description:event]]
     *
     * @return [[@doctodo return_type:subactionHandle]] [[@doctodo return_description:subactionHandle]]
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
     * [[@doctodo method_description:subactions]].
     *
     * @return [[@doctodo return_type:subactions]] [[@doctodo return_description:subactions]]
     */
    public function subactions()
    {
        return [];
    }

    /**
     * Set up.
     *
     * @return [[@doctodo return_type:setup]] [[@doctodo return_description:setup]]
     */
    public function setup()
    {
        $results = [true];
        if (!empty($this->primaryModel) && !empty($this->collectorItem->parents)) {
            $groups = ['top'];
            foreach ($groups as $groupName) {
                $group = Group::getBySystemName($groupName, false);
                if (empty($group)) {
                    continue;
                }
                if ($this->inheritParentAccess) {
                    $results[] = $this->objectTypeModel->parentAccess(null, $group);
                }
            }
        }

        return min($results);
    }

    /**
     * Get inherit parent access.
     *
     * @return [[@doctodo return_type:getInheritParentAccess]] [[@doctodo return_description:getInheritParentAccess]]
     */
    public function getInheritParentAccess()
    {
        return !$this->hasDashboard;
    }

    /**
     * [[@doctodo method_description:determineOwner]].
     *
     * @return [[@doctodo return_type:determineOwner]] [[@doctodo return_description:determineOwner]]
     */
    public function determineOwner($object)
    {
        if (isset(Yii::$app->user)
            && !Yii::$app->user->isGuest
            && isset(Yii::$app->user->identity)
            ) {
            if (!empty(Yii::$app->user->identity->object_individual_id)) {
                return Yii::$app->user->identity->object_individual_id;
            } else {
                return false; //Yii::$app->user->id;
            }
        }

        return false;
    }

    /**
     * Get role help text.
     *
     * @return [[@doctodo return_type:getRoleHelpText]] [[@doctodo return_description:getRoleHelpText]]
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

        return;
    }

    /**
     * [[@doctodo method_description:determineAccessLevel]].
     *
     * @return [[@doctodo return_type:determineAccessLevel]] [[@doctodo return_description:determineAccessLevel]]
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
     * Get action map.
     *
     * @return [[@doctodo return_type:getActionMap]] [[@doctodo return_description:getActionMap]]
     */
    public function getActionMap($controlledObject = null)
    {
        return [];
    }

    /**
     * Get primary as child.
     *
     * @param cascade\components\types\Module $parent [[@doctodo param_description:parent]]
     *
     * @return [[@doctodo return_type:getPrimaryAsChild]] [[@doctodo return_description:getPrimaryAsChild]]
     */
    public function getPrimaryAsChild(Module $parent)
    {
        return false;
    }

    /**
     * Get primary as parent.
     *
     * @param cascade\components\types\Module $child [[@doctodo param_description:child]]
     *
     * @return [[@doctodo return_type:getPrimaryAsParent]] [[@doctodo return_description:getPrimaryAsParent]]
     */
    public function getPrimaryAsParent(Module $child)
    {
        return false;
    }

    /**
     * Get disabled fields.
     *
     * @return [[@doctodo return_type:getDisabledFields]] [[@doctodo return_description:getDisabledFields]]
     */
    public function getDisabledFields()
    {
        if (is_null($this->_disabledFields)) {
            return [];
        }

        return $this->_disabledFields;
    }

    /**
     * Set disabled fields.
     */
    public function setDisabledFields($fields)
    {
        $this->_disabledFields = $fields;
    }

    /**
     * Get primary model.
     *
     * @return [[@doctodo return_type:getPrimaryModel]] [[@doctodo return_description:getPrimaryModel]]
     */
    public function getPrimaryModel()
    {
        return $this->modelNamespace . '\\' . 'Object' . $this->systemId;
    }

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Type';
    }

    /**
     * Get insert verb.
     *
     * @return [[@doctodo return_type:getInsertVerb]] [[@doctodo return_description:getInsertVerb]]
     */
    public function getInsertVerb($object)
    {
        return;
    }

    /**
     * Get update verb.
     *
     * @return [[@doctodo return_type:getUpdateVerb]] [[@doctodo return_description:getUpdateVerb]]
     */
    public function getUpdateVerb($object)
    {
        return;
    }
    /**
     * Get delete verb.
     *
     * @return [[@doctodo return_type:getDeleteVerb]] [[@doctodo return_description:getDeleteVerb]]
     */
    public function getDeleteVerb($object)
    {
        return;
    }

    /**
     * [[@doctodo method_description:upgrade]].
     *
     * @return [[@doctodo return_type:upgrade]] [[@doctodo return_description:upgrade]]
     */
    public function upgrade($from)
    {
        return $this->setup();
    }

    /**
     * Get creator role.
     *
     * @return [[@doctodo return_type:getCreatorRole]] [[@doctodo return_description:getCreatorRole]]
     */
    public function getCreatorRole()
    {
        return [];
    }

    /**
     * Get role validation settings.
     *
     * @return [[@doctodo return_type:getRoleValidationSettings]] [[@doctodo return_description:getRoleValidationSettings]]
     */
    public function getRoleValidationSettings($object = null)
    {
        $settings = [];
        $settings['possibleRoles'] = $this->possibleRoles;

        return $settings;
    }

    /**
     * Get possible roles.
     *
     * @return [[@doctodo return_type:getPossibleRoles]] [[@doctodo return_description:getPossibleRoles]]
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
     * Get required roles.
     *
     * @return [[@doctodo return_type:getRequiredRoles]] [[@doctodo return_description:getRequiredRoles]]
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
     * Get initial role.
     *
     * @return [[@doctodo return_type:getInitialRole]] [[@doctodo return_description:getInitialRole]]
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
     * Get is ownable.
     *
     * @return [[@doctodo return_type:getIsOwnable]] [[@doctodo return_description:getIsOwnable]]
     */
    public function getIsOwnable()
    {
        return $this->dummyModel->getBehavior('Ownable') !== null && $this->dummyModel->getBehavior('Ownable')->isEnabled();
    }

    /**
     * Get owner object.
     *
     * @return [[@doctodo return_type:getOwnerObject]] [[@doctodo return_description:getOwnerObject]]
     */
    public function getOwnerObject()
    {
        return;
    }

    /**
     * Get owner.
     *
     * @return [[@doctodo return_type:getOwner]] [[@doctodo return_description:getOwner]]
     */
    public function getOwner()
    {
        if (!$this->isOwnable) {
            return;
        }
        $ownerObject = $this->getOwnerObject();
        if (is_object($ownerObject)) {
            return $ownerObject->primaryKey;
        }

        return $ownerObject;
    }

    /**
     * Get object type model.
     *
     * @return [[@doctodo return_type:getObjectTypeModel]] [[@doctodo return_description:getObjectTypeModel]]
     */
    public function getObjectTypeModel()
    {
        if (!isset($this->_objectTypeModel) && isset(Yii::$app->collectors['types']->tableRegistry[$this->systemId])) {
            $this->_objectTypeModel = Yii::$app->collectors['types']->tableRegistry[$this->systemId];
        }

        return $this->_objectTypeModel;
    }

    /**
     * Set object type model.
     */
    public function setObjectTypeModel($model)
    {
        $this->_objectTypeModel = $model;
    }
    /**
     * [[@doctodo method_description:search]].
     *
     * @param unknown $term
     * @param array   $params [[@doctodo param_description:params]] [optional]
     *
     * @return unknown
     */
    public function search($term, $params = [])
    {
        if (!$this->primaryModel) {
            return false;
        }

        $results = [];
        $modelClass = $this->primaryModel;

        return $modelClass::searchTerm($term, $params);
    }

    /**
     * Get object level.
     *
     * @return [[@doctodo return_type:getObjectLevel]] [[@doctodo return_description:getObjectLevel]]
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
                if (get_class($rel->parent) === get_class($this)) {
                    continue;
                }
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
     * Get section.
     *
     * @param unknown $settings (optional)
     *
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
            $sectionId = $settings['relationship']->systemId . '-' . $this->systemId;
            $section = Yii::$app->collectors['sections']->getOne($sectionId);
            $section->title = '%%relationship%% %%type.' . $this->systemId . '.title.upperPlural%%';
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
        $newSectionTitle = '%%type.' . $this->systemId . '.title.upperPlural%%';
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
     * Get title.
     *
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
     * Set title.
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * Get details widget.
     *
     * @return [[@doctodo return_type:getDetailsWidget]] [[@doctodo return_description:getDetailsWidget]]
     */
    public function getDetailsWidget($objectModel = null)
    {
        if (is_null($objectModel) && isset(Yii::$app->request->object)) {
            $objectModel = Yii::$app->request->object;
        } elseif (is_null($objectModel)) {
            $objectModel = $this->dummyModel;
        }

        $detailsSection = $this->getDetailsSection();
        if ($detailsSection === false) {
            return false;
        }
        if ($detailsSection === true) {
            $detailsSection = '_self';
        }

        $detailsWidgetClass = self::classNamespace() . '\widgets\\' . 'Details';
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
     * Get details section.
     *
     * @return [[@doctodo return_type:getDetailsSection]] [[@doctodo return_description:getDetailsSection]]
     */
    public function getDetailsSection()
    {
        return '_side';
    }

    /**
     * [[@doctodo method_description:widgets]].
     *
     * @return [[@doctodo return_type:widgets]] [[@doctodo return_description:widgets]]
     */
    public function widgets()
    {
        $widgets = [];

        $detailsWidget = $this->getDetailsWidget();
        if ($detailsWidget) {
            $id = '_' . $this->systemId . 'Details';
            $widgets[$id] = $detailsWidget;
        }

        $detailListClassName = self::classNamespace() . '\widgets\\' . 'DetailList';
        $simpleListClassName = self::classNamespace() . '\widgets\\' . 'SimpleLinkList';
        @class_exists($detailListClassName);
        @class_exists($simpleListClassName);

        $baseWidget = [];
        if ($this->module instanceof \cascade\components\section\Module) {
            $baseWidget['section'] = $this->module->collectorItem;
        }

        if (!$this->isChildless) {
            if (!class_exists($detailListClassName, false)) {
                $detailListClassName = false;
            }
            if (!class_exists($simpleListClassName, false)) {
                $simpleListClassName = false;
            }
            // needs widget for children and summary page
            if ($detailListClassName) {
                $childrenWidget = $baseWidget;
                $id = 'Parent' . $this->systemId . 'Browse';
                $childrenWidget['widget'] = [
                    'class' => $detailListClassName,
                    'icon' => $this->icon,
                    'title' => '%%relationship%% %%type.' . $this->systemId . '.title.upperPlural%%',
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
                $id = $this->systemId . 'Summary';
                $summaryWidget['widget'] = [
                    'class' => $simpleListClassName,
                    'icon' => $this->icon,
                    'title' => '%%type.' . $this->systemId . '.title.upperPlural%%',
                ];
                $summaryWidget['locations'] = ['front'];
                $summaryWidget['priority'] = $this->priority;
                $widgets[$id] = $summaryWidget;
            } else {
                Yii::trace("Warning: There is no summary class for {$this->systemId}");
            }
        } else {
            if (!class_exists($detailListClassName, false)) {
                $detailListClassName = false;
            }
            // needs widget for parents
        }
        if ($detailListClassName) {
            $parentsWidget = $baseWidget;
            $id = 'Children' . $this->systemId . 'Browse';
            $parentsWidget['widget'] = [
                    'class' => $detailListClassName,
                    'icon' => $this->icon,
                    'title' => '%%relationship%% %%type.' . $this->systemId . '.title.upperPlural%%',
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
     * [[@doctodo method_description:loadFieldLink]].
     *
     * @param boolean $typeMatch [[@doctodo param_description:typeMatch]] [optional]
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
     * [[@doctodo method_description:taxonomies]].
     *
     * @return unknown
     */
    public function taxonomies()
    {
        return [];
    }

    /**
     * [[@doctodo method_description:roles]].
     *
     * @return unknown
     */
    public function roles()
    {
        return [];
    }

    /**
     * [[@doctodo method_description:dependencies]].
     *
     * @return unknown
     */
    public function dependencies()
    {
        return [];
    }

    /**
     * [[@doctodo method_description:parents]].
     *
     * @return unknown
     */
    public function parents()
    {
        return [];
    }

    /**
     * Settings for.
     *
     * @return unknown
     */
    public function parentSettings()
    {
        return [
            'title' => false,
            'allow' => 1, // 0/false = no; 1 = only 1; 2 = 1 or more
            'showDescriptor' => false,
        ];
    }

    /**
     * [[@doctodo method_description:childrenSettings]].
     *
     * @return unknown
     */
    public function childrenSettings()
    {
        return [
            'allow' => 2,  // 0/false = no; 1 = only 1; 2 = 1 or more
        ];
    }

    /**
     * [[@doctodo method_description:children]].
     *
     * @return unknown
     */
    public function children()
    {
        return [];
    }

    /**
     * Get dummy model.
     *
     * @return [[@doctodo return_type:getDummyModel]] [[@doctodo return_description:getDummyModel]]
     */
    public function getDummyModel()
    {
        if (!$this->primaryModel) {
            return false;
        }
        $model = $this->primaryModel;

        return new $model();
    }

    /**
     * Get is childless.
     *
     * @return [[@doctodo return_type:getIsChildless]] [[@doctodo return_description:getIsChildless]]
     */
    public function getIsChildless()
    {
        if (empty($this->collectorItem) || empty($this->collectorItem->children)) {
            return true;
        }

        return false;
    }

    /**
     * Get model.
     *
     * @param boolean $input [[@doctodo param_description:input]] [optional]
     *
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
                $primaryModel = new $primaryModel();
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
     * Get form.
     *
     * @param boolean $primaryModel [[@doctodo param_description:primaryModel]] [optional]
     * @param array   $settings     [[@doctodo param_description:settings]] [optional]
     *
     * @return unknown
     */
    public function getForm($primaryModel = false, $settings = [])
    {
        if (!$primaryModel) {
            return false;
        }
        $formSegments = [$this->getFormSegment($primaryModel, $settings)];
        $config = ['class' => $this->formGeneratorClass, 'models' => $primaryModel->collectModels(), 'items' => $formSegments];

        return Yii::createObject($config);
    }

    /**
     * Get form segment.
     *
     * @param array $settings [[@doctodo param_description:settings]] [optional]
     *
     * @return [[@doctodo return_type:getFormSegment]] [[@doctodo return_description:getFormSegment]]
     */
    public function getFormSegment($primaryModel = null, $settings = [])
    {
        if (empty($primaryModel)) {
            return false;
        }

        return $primaryModel->form($settings);
    }
}
