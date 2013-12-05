<?php

namespace cascade\models;

/**
 * This is the model class for table "relation_taxonomy".
 *
 * @property string $id
 * @property string $relation_id
 * @property string $taxonomy_id
 *
 * @property Taxonomy $taxonomy
 * @property Relation $relation
 */
class RelationTaxonomy extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'relation_taxonomy';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['relation_id', 'taxonomy_id'], 'required'],
			[['relation_id'], 'integer'],
			[['taxonomy_id'], 'string', 'max' => 36]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'relation_id' => 'Relation ID',
			'taxonomy_id' => 'Taxonomy ID',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getTaxonomy()
	{
		return $this->hasOne(Taxonomy::className(), ['id' => 'taxonomy_id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	// public function getRelation()
	// {
	// 	return $this->hasOne(Relation::className(), ['id' => 'relation_id']);
	// }
}
