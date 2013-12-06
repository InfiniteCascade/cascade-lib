<?php

namespace cascade\modules\TypeEmailAddress;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'Email Address';
	public $icon = 'fa fa-envelope-o';
	public $uniparental = true;
	public $hasDashboard = false;

	public $widgetNamespace = 'cascade\modules\TypeEmailAddress\widgets';
	public $modelNamespace = 'cascade\modules\TypeEmailAddress\models';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('@cascade/modules/TypeEmailAddress/migrations');
	}

	/**
	 * @inheritdoc
	 */
	public function widgets()
	{
		$widgets = parent::widgets();
		$widgets['EmbeddedEmailAddressBrowse']['section'] = Yii::$app->collectors['sections']->getOne('_side');
		return $widgets;
	}

	
	/**
	 * @inheritdoc
	 */
	public function parents()
	{
		return [
			'Individual' => [],
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