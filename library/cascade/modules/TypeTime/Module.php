<?php

namespace cascade\modules\TypeTime;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'Time';
	public $icon = 'fa fa-clock-o';
	public $uniparental = false;
	public $hasDashboard = false;

	public $widgetNamespace = 'cascade\modules\TypeTime\widgets';
	public $modelNamespace = 'cascade\modules\TypeTime\models';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('@cascade/modules/TypeTime/migrations');
	}

	/**
	 * @inheritdoc
	 */
	public function widgets()
	{
			return parent::widgets();
	}

	
	/**
	 * @inheritdoc
	 */
	public function parents()
	{
		return [
			'Project' => [],
			'Individual' => [],
		];
	}

	
	/**
	 * @inheritdoc
	 */
	public function children()
	{
		return [
			'File' => ['uniqueChild' => true],
			'Note' => ['uniqueChild' => true],
];
	}

	
	/**
	 * @inheritdoc
	 */
	public function taxonomies()
	{
		return [];
	}
}