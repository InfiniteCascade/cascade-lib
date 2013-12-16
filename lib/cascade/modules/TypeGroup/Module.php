<?php

namespace cascade\modules\TypeGroup;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'Group';
	public $icon = 'fa fa-users';
	public $uniparental = false;
	public $hasDashboard = true;

	public $widgetNamespace = 'cascade\modules\Group\widgets';
	public $modelNamespace = false;

	/**
	 * @inheritdoc
	 */
	public function getPrimaryModel() {
		return 'cascade\\models\\Group';
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
			'Group' => ['handlePrimary' => false],
		];
	}

	
	/**
	 * @inheritdoc
	 */
	public function children()
	{
		return [
			'User' => ['uniqueChild' => true, 'handlePrimary' => false],
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