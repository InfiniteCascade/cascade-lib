<?php
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
