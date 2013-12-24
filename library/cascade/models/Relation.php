<?php

namespace cascade\models;

use Yii;

use cascade\components\db\ActiveRecordTrait;
use cascade\components\types\Relationship;

class Relation extends \infinite\db\models\Relation
{
	use ActiveRecordTrait;

	public $registryClass = 'cascade\\models\\Registry';

	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'Taxonomy' => [
				'class' => 'cascade\\components\\db\\behaviors\\Taxonomy',
				'viaModelClass' => 'cascade\\models\\RelationTaxonomy',
				'relationKey' => 'relation_id'
			],
			'PrimaryRelation' => [
				'class' => 'cascade\\components\\db\\behaviors\\PrimaryRelation'
			]
		]);
	}

	public function addFields($caller, &$fields, $relationship, $owner) {
		$baseField = ['model' => $this];
		if (isset($this->id)) {
			$fields['relation:id'] = $caller->createField('id', $owner, $baseField);
		}
		if (!empty($relationship->taxonomy) 
				&& ($taxonomyItem = Yii::$app->collectors['taxonomies']->getOne($relationship->taxonomy)) 
				&& ($taxonomy = $taxonomyItem->object) 
				&& $taxonomy) {
			$fieldName = 'relation:taxonomy_id';
			$fieldSchema = $caller->createColumnSchema('taxonomy_id', ['type' => 'taxonomy', 'phpType' => 'object', 'dbType' => 'taxonomy', 'allowNull' => true]);

			$fields[$fieldName] = $caller->createTaxonomyField($fieldSchema, $taxonomyItem, $owner, $baseField);
			//var_dump($fields['relation:taxonomy_id']);exit;
		}
	}

	public function getRelationship()
	{
		if (!isset($this->parentObject) || !isset($this->parentObject->objectTypeItem)) { return false; }
		if (!isset($this->childObject) || !isset($this->childObject->objectTypeItem)) { return false; }
		return Relationship::getOne($this->parentObject->objectTypeItem, $this->childObject->objectTypeItem);
	}
}
