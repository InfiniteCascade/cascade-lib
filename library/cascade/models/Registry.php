<?php

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

class Registry extends \infinite\db\models\Registry
{
	use ActiveRecordTrait;

	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'Relatable' => [
				'class' => 'infinite\\db\\behaviors\\Relatable',
			],
		]);
	}
}