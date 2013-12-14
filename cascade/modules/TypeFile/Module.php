<?php

namespace cascade\modules\TypeFile;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'File';
	public $icon = 'fa fa-paperclip';
	public $uniparental = false;
	public $hasDashboard = false;

	public $widgetNamespace = 'cascade\modules\TypeFile\widgets';
	public $modelNamespace = 'cascade\modules\TypeFile\models';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('@cascade/modules/TypeFile/migrations');
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
			'Account' => [],
			'Individual' => [],
			'Time' => [],
			'Note' => [],
			'Task' => [],
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