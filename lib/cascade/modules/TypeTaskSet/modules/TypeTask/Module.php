<?php

namespace cascade\modules\TypeTaskSet\modules\TypeTask;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'Task';
	public $icon = 'fa fa-check';
	public $uniparental = true;
	public $hasDashboard = false;

	public $widgetNamespace = 'cascade\modules\TypeTaskSet\modules\TypeTask\widgets';
	public $modelNamespace = 'cascade\modules\TypeTaskSet\modules\TypeTask\models';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('@cascade/modules/TypeTaskSet/modules/TypeTask/migrations');
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
			'TaskSet' => [],
		];
	}

	
	/**
	 * @inheritdoc
	 */
	public function children()
	{
		return [];
	}

	
	/**
	 * @inheritdoc
	 */
	public function taxonomies()
	{
		return [];
	}
}