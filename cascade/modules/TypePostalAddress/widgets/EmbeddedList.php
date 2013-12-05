<?php
namespace cascade\modules\TypePostalAddress\widgets;

use infinite\helpers\Html;

class EmbeddedList extends \cascade\components\web\widgets\base\SideList
{
	public $renderContentTemplate = ['name' => ['class' => 'list-group-item-heading', 'tag' => 'h5'], 'address1', 'address2', 'csz', 'uniqueCountry'];
}
