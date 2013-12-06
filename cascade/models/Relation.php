<?php

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

class Relation extends \infinite\db\models\Relation
{
	use ActiveRecordTrait;
	
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'Taxonomy' => [
				'class' => 'cascade\\components\\db\\behaviors\\Taxonomy',
				'viaModelClass' => 'cascade\\models\\RelationTaxonomy',
				'relationKey' => 'relation_id'
			]
		]);
	}
}
