<?php
namespace cascade\components\section\base;

class Side extends \cascade\components\section\Section {
	public $sectionWidgetClass = 'cascade\components\web\widgets\base\SideSection';
	protected $_systemId = '_side';
	protected $_title = 'Side Panel';
}
?>