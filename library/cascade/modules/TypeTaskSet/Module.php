<?php

namespace cascade\modules\TypeTaskSet;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'Task Set';
	public $icon = 'fa fa-list';
	public $uniparental = false;
	public $hasDashboard = false;

	public $widgetNamespace = 'cascade\modules\TypeTaskSet\widgets';
	public $modelNamespace = 'cascade\modules\TypeTaskSet\models';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('@cascade/modules/TypeTaskSet/migrations');
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