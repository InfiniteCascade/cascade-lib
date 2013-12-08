<?php

namespace cascade\models;

use Yii;

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

	public function addFields($caller, &$fields, $relationship, $owner) {
		if (!empty($relationship->taxonomy) 
				&& ($taxonomyItem = Yii::$app->collectors['taxonomies']->getOne($relationship->taxonomy)) 
				&& ($taxonomy = $taxonomyItem->object) 
				&& $taxonomy) {
			$fields['relation:taxonomy_id'] = $caller->createTaxonomyField($taxonomyItem, $owner, ['model' => $this]);
			//var_dump($fields['relation:taxonomy_id']);exit;
		}
	}
}
