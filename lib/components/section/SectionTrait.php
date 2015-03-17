<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\section;

use cascade\components\helpers\StringHelper;
use canis\base\collector\CollectorTrait;
use canis\web\RenderTrait;
use yii\helpers\Inflector;

trait SectionTrait
{
    use CollectorTrait;
    //use RenderTrait;

    public $sectionWidgetClass = 'cascade\components\web\widgets\section\Section';
    public $sectionSingleWidgetClass = 'cascade\components\web\widgets\section\SingleSection';
    public $gridCellClass = 'canis\web\grid\Cell';

    protected $_title;
    protected $_parsedTitle;
    protected $_widget;
    protected $_gridCell;
    /**
     */
    protected $_priority = 0;

    /**
     * Get priority.
     */
    public function getPriority()
    {
        if (isset($this->object->singleWidget)) {
            if (isset($this->object->singleWidget) && isset($this->object->singleWidget->content->priorityAdjust)) {
                //\d($this->object->singleWidget->content->priorityAdjust);exit;
                return $this->_priority + $this->object->singleWidget->content->priorityAdjust;
            }
        }

        return $this->_priority;
    }

    /**
     * Set priority.
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }

    public $icon = 'fa fa-info';

    public function init()
    {
        parent::init();
        $this->registerMultiple($this, $this->defaultItems());
    }

    public static function generateSectionId($name)
    {
        return Inflector::slug($name);
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return unknown
     */
    public function getTitle()
    {
        return $this->_title;
    }

    public function getSectionTitle()
    {
        if (!isset($this->_parsedTitle)) {
            $this->_parsedTitle = StringHelper::parseText($this->title);
        }

        return $this->_parsedTitle;
    }

    /**
     * @param unknown $parent (optional)
     *
     * @return unknown
     */
    protected function defaultItems($parent = null)
    {
        return [];
    }
}
