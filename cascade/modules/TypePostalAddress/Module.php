<?php

namespace cascade\modules\TypePostalAddress;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = 'Postal Address';
	public $icon = 'fa fa-envelope';
	public $uniparental = true;
	public $hasDashboard = false;

	public $widgetNamespace = 'cascade\modules\TypePostalAddress\widgets';
	public $modelNamespace = 'cascade\modules\TypePostalAddress\models';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('@cascade/modules/TypePostalAddress/migrations');
	}

	/**
	 * @inheritdoc
	 */
	public function widgets()
	{
		$widgets = parent::widgets();
		$widgets['EmbeddedPostalAddressBrowse']['section'] = Yii::$app->collectors['sections']->getOne('_side');
		return $widgets;
	}

	
	/**
	 * @inheritdoc
	 */
	public function parents()
	{
		return [
			'Account' => ['taxonomy' => 'ic_address_type'],
			'Individual' => ['taxonomy' => 'ic_address_type'],
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
		return [
			[
				'name' => 'Address Type',
				'models' => [\cascade\models\Relation::className()],
				'modules' => [self::className()],
				'systemId' => 'ic_address_type',
				'systemVersion' => 1.0,
				'multiple' => true,
				'parentUnique' => true,
				'required' => true,
				'initialTaxonomies' => [
					'billing' => 'Billing Address',
					'shipping' => 'Shipping Address',
				]
			]
		];
	}
}