<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use Yii;
use infinite\helpers\ArrayHelper;

/**
 * Item [@doctodo write class description for Item]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \infinite\base\collector\Item
{
    /**
     * @var __var__children_type__ __var__children_description__
     */
    protected $_children = [];
    /**
     * @var __var__parents_type__ __var__parents_description__
     */
    protected $_parents = [];
    /**
     * @var __var__sections_type__ __var__sections_description__
     */
    protected $_sections;
    /**
     * @var __var__checked_type__ __var__checked_description__
     */
    protected $_checked;
    /**
     * @var __var__init_type__ __var__init_description__
     */
    protected $_init = false;

    /**
    * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_init = true;
        $this->distributeRelationships();
    }

    /**
    * @inheritdoc
     */
    public function setObject($object)
    {
        parent::setObject($object);
        $this->distributeRelationships();

        return true;
    }

    /**
     * __method_distributeRelationships_description__
     * @return __return_distributeRelationships_type__ __return_distributeRelationships_description__
     */
    protected function distributeRelationships()
    {
        if (!$this->_init || is_null($this->object)) {
            return;
        }

        foreach ($this->object->children() as $key => $child) {
            $options = [];
            if (is_string($key)) {
                $options = $child;
                $child = $key;
            }
            $this->collector->addRelationship($this->systemId, $child, $options);
        }
        foreach ($this->object->parents() as $key => $parent) {
            $options = [];
            if (is_string($key)) {
                $options = $parent;
                $parent = $key;
            }
            $this->collector->addRelationship($parent, $this->systemId, $options);
        }
    }

    /**
     * Get sections
     * @return unknown
     */
    public function getSections()
    {
        if (!is_null($this->_sections)) {
            return $this->_sections;
        }
        $this->_sections = [];
        foreach ($this->_children as $rel) {
            if (!$rel->active) { continue; }
            // if ($rel->isHasOne()) { continue; }
            $child = $rel->child;
            $instanceSettings = ['relationship' => $rel, 'queryRole' => 'children'];
            $items = Yii::$app->collectors['widgets']->getLocation('parent_objects', $child);
            foreach ($items as $item) {
                $widgetObject = $item->object;
                $item->settings = $instanceSettings;
                $section = $item->getSection($widgetObject, $instanceSettings);
                if (empty($section)) { continue; }
                if (!isset($this->_sections[$item->section->systemId])) {
                    $this->_sections[$section->systemId] = $section;
                }
                $this->_sections[$section->systemId]->register($this, $item);
            }
        }

        foreach ($this->_parents as $rel) {
            if (!$rel->active) { continue; }
            if ($rel->isHasOne()) { continue; }
            $parent = $rel->parent;
            $instanceSettings = ['relationship' => $rel, 'queryRole' => 'parents'];
            $items = Yii::$app->collectors['widgets']->getLocation('child_objects', $parent);
            foreach ($items as $item) {
                $item->settings = $instanceSettings;
                $section = $item->getSection($this->object);
                if (empty($section)) { continue; }
                if (!isset($this->_sections[$item->section->systemId])) {
                    $this->_sections[$section->systemId] = $section;
                }
                $this->_sections[$section->systemId]->register($this, $item);
            }
        }
        $items = Yii::$app->collectors['widgets']->getLocation('self',  $this->object);
        foreach ($items as $item) {
            $item->settings = $instanceSettings;
            $section = $item->getSection($this->object);
            if (empty($section)) { continue; }
            if (!isset($this->_sections[$item->section->systemId])) {
                $this->_sections[$section->systemId] = $section;
            }
            $this->_sections[$section->systemId]->register($this, $item);
        }

        ArrayHelper::multisort($this->_sections, ['priority', 'sectionTitle'], [SORT_ASC, SORT_ASC]);

        return $this->_sections;
    }

    /**
     * Get widgets
     * @return __return_getWidgets_type__ __return_getWidgets_description__
     */
    public function getWidgets()
    {
        $sections = $this->sections;
        $widgets = [];
        foreach ($this->sections as $section) {
            foreach ($section->getAll() as $key => $widget) {
                $widgets[$key] = $widget;
            }
        }

        return $widgets;
    }
    /**
     * __method_addChild_description__
     * @param unknown $name
     * @param unknown $relationship
     * @return unknown
     */
    public function addChild($name, $relationship)
    {
        $this->_children[$name] = $relationship;

        return true;
    }

    /**
     * __method_addParent_description__
     * @param unknown $name
     * @param unknown $relationship
     * @return unknown
     */
    public function addParent($name, $relationship)
    {
        $this->_parents[$name] = $relationship;

        return true;
    }

    /**
     * Get child
     * @param unknown $type
     * @return unknown
     */
    public function getChild($type)
    {
        if (isset($this->_children[$type])) {
            return $this->_children[$type];
        }

        return false;
    }

    /**
     * Get parent
     * @param unknown $type
     * @return unknown
     */
    public function getParent($type)
    {
        if (isset($this->_parents[$type])) {
            return $this->_parents[$type];
        }

        return false;
    }

    /**
     * Get children
     * @return unknown
     */
    public function getChildren()
    {
        $children = [];
        foreach ($this->_children as $key => $child) {
            if (!$child->active) { continue; }
            $children[$key] = $child;
        }

        return $children;
    }

    /**
     * Get parents
     * @return unknown
     */
    public function getParents()
    {
        $parents = [];
        foreach ($this->_parents as $key => $parent) {
            if (!$parent->active) { continue; }
            $parents[$key] = $parent;
        }

        return $parents;
    }

    /**
     * Get active
     * @return unknown
     */
    public function getActive()
    {
        if (!is_null($this->hasObject()) && $this->checked) {
            return true;
        }

        return false;
    }

    /**
     * Get checked
     * @return unknown
     */
    public function getChecked()
    {
        if (is_null($this->object) || !$this->object) { return false; }
        if (is_null($this->_checked)) {
            $this->_checked = true;
            foreach ($this->object->dependencies() as $dep) {
                if (!$this->collector->has($dep, false)) {
                    $this->_checked = false;
                }
            }
        }

        return $this->_checked;
    }

    /**
     * Get taxonomies
     * @return __return_getTaxonomies_type__ __return_getTaxonomies_description__
     */
    public function getTaxonomies()
    {
        $moduleClass = get_class($this->object);

        return Yii::$app->collectors['taxonomies']->getBucket('modules:'. $moduleClass::className())->toArray();
    }
}
