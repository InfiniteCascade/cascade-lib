<?php

namespace cascade\modules\TypeProject;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'Project';
	public $icon = 'fa fa-briefcase';
	public $uniparental = false;
	public $hasDashboard = true;

	public $widgetNamespace = 'cascade\modules\TypeProject\widgets';
	public $modelNamespace = 'cascade\modules\TypeProject\models';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('@cascade/modules/TypeProject/migrations');
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
			'Individual' => [],
			'Account' => [],
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
			'Time' => ['uniqueChild' => true],
			'TaskSet' => ['uniqueChild' => true],
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