<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\types;

use canis\helpers\ArrayHelper;
use Yii;

/**
 * Item [[@doctodo class_description:cascade\components\types\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \canis\base\collector\Item
{
    /**
     * @var [[@doctodo var_type:_children]] [[@doctodo var_description:_children]]
     */
    protected $_children = [];
    /**
     * @var [[@doctodo var_type:_parents]] [[@doctodo var_description:_parents]]
     */
    protected $_parents = [];
    /**
     * @var [[@doctodo var_type:_sections]] [[@doctodo var_description:_sections]]
     */
    protected $_sections;
    /**
     * @var [[@doctodo var_type:_checked]] [[@doctodo var_description:_checked]]
     */
    protected $_checked;
    /**
     * @var [[@doctodo var_type:_init]] [[@doctodo var_description:_init]]
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
     * [[@doctodo method_description:distributeRelationships]].
     *
     * @return [[@doctodo return_type:distributeRelationships]] [[@doctodo return_description:distributeRelationships]]
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
     * Get sections.
     *
     * @return unknown
     */
    public function getSections()
    {
        if (!is_null($this->_sections)) {
            return $this->_sections;
        }
        $this->_sections = [];
        foreach ($this->_children as $rel) {
            if (!$rel->active) {
                continue;
            }
            // if ($rel->isHasOne()) { continue; }
            $child = $rel->child;
            $instanceSettings = ['relationship' => $rel, 'queryRole' => 'children'];
            $items = Yii::$app->collectors['widgets']->getLocation('parent_objects', $child);
            foreach ($items as $item) {
                $widgetObject = $item->object;
                $item->settings = $instanceSettings;
                $section = $item->getSection($widgetObject, $instanceSettings);
                if (empty($section)) {
                    continue;
                }
                if (!isset($this->_sections[$item->section->systemId])) {
                    $this->_sections[$section->systemId] = $section;
                }
                $this->_sections[$section->systemId]->register($this, $item);
            }
        }

        foreach ($this->_parents as $rel) {
            if (!$rel->active) {
                continue;
            }
            if ($rel->isHasOne()) {
                continue;
            }
            $parent = $rel->parent;
            $instanceSettings = ['relationship' => $rel, 'queryRole' => 'parents'];
            $items = Yii::$app->collectors['widgets']->getLocation('child_objects', $parent);
            foreach ($items as $item) {
                $item->settings = $instanceSettings;
                $section = $item->getSection($this->object);
                if (empty($section)) {
                    continue;
                }
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
            if (empty($section)) {
                continue;
            }
            if (!isset($this->_sections[$item->section->systemId])) {
                $this->_sections[$section->systemId] = $section;
            }
            $this->_sections[$section->systemId]->register($this, $item);
        }

        ArrayHelper::multisort($this->_sections, ['priority', 'sectionTitle'], [SORT_ASC, SORT_ASC]);

        return $this->_sections;
    }

    /**
     * Get widgets.
     *
     * @return [[@doctodo return_type:getWidgets]] [[@doctodo return_description:getWidgets]]
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
     * [[@doctodo method_description:addChild]].
     *
     * @param unknown $name
     * @param unknown $relationship
     *
     * @return unknown
     */
    public function addChild($name, $relationship)
    {
        $this->_children[$name] = $relationship;

        return true;
    }

    /**
     * [[@doctodo method_description:addParent]].
     *
     * @param unknown $name
     * @param unknown $relationship
     *
     * @return unknown
     */
    public function addParent($name, $relationship)
    {
        $this->_parents[$name] = $relationship;

        return true;
    }

    /**
     * Get child.
     *
     * @param unknown $type
     *
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
     * Get parent.
     *
     * @param unknown $type
     *
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
     * Get children.
     *
     * @return unknown
     */
    public function getChildren()
    {
        $children = [];
        foreach ($this->_children as $key => $child) {
            if (!$child->active) {
                continue;
            }
            $children[$key] = $child;
        }

        return $children;
    }

    /**
     * Get parents.
     *
     * @return unknown
     */
    public function getParents()
    {
        $parents = [];
        foreach ($this->_parents as $key => $parent) {
            if (!$parent->active) {
                continue;
            }
            $parents[$key] = $parent;
        }

        return $parents;
    }

    /**
     * Get active.
     *
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
     * Get checked.
     *
     * @return unknown
     */
    public function getChecked()
    {
        if (is_null($this->object) || !$this->object) {
            return false;
        }
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
     * Get taxonomies.
     *
     * @return [[@doctodo return_type:getTaxonomies]] [[@doctodo return_description:getTaxonomies]]
     */
    public function getTaxonomies()
    {
        $moduleClass = get_class($this->object);

        return Yii::$app->collectors['taxonomies']->getBucket('modules:' . $moduleClass::className())->toArray();
    }
}
