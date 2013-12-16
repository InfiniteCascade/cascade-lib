<?php

namespace cascade\modules\TypeWebAddress\widgets;

class EmbeddedList extends \cascade\components\web\widgets\base\SideList
{
	public $renderContentTemplate = ['link' => ['class' => 'list-group-item-heading', 'tag' => 'h5']];
}
