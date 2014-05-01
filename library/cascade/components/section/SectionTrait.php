<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\section;

use Yii;

use yii\helpers\Inflector;
use cascade\components\helpers\StringHelper;
use infinite\base\collector\CollectorTrait;
use infinite\web\RenderTrait;

trait SectionTrait
{
    use CollectorTrait;
    //use RenderTrait;

    public $sectionWidgetClass = 'cascade\\components\\web\\widgets\\section\\Section';
    public $sectionSingleWidgetClass = 'cascade\\components\\web\\widgets\\section\\SingleSection';
    public $gridCellClass = 'infinite\\web\\grid\\Cell';

    protected $_title;
    protected $_parsedTitle;
    protected $_widget;
    protected $_gridCell;
    /**
     * @var __var__priority_type__ __var__priority_description__
     */
    protected $_priority = 0;

    /**
     * Get priority
     * @return __return_getPriority_type__ __return_getPriority_description__
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
     * Set priority
     * @param __param_priority_type__ $priority __param_priority_description__
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
     *
     *
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
     *
     *
     * @param  unknown $parent (optional)
     * @return unknown
     */
    protected function defaultItems($parent = null)
    {
        return [];
    }

}
