<?php

namespace cascade\models;

/**
 * This is the model class for table "taxonomy".
 *
 * @property string $id
 * @property string $taxonomy_type_id
 * @property string $name
 * @property string $system_id
 * @property string $created
 * @property string $modified
 *
 * @property RelationTaxonomy[] $relationTaxonomies
 * @property TaxonomyType $taxonomyType
 * @property Registry $id
 */
class Taxonomy extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'taxonomy';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'taxonomy_type_id', 'name'], 'required'],
			[['created', 'modified'], 'safe'],
			[['id', 'taxonomy_type_id'], 'string', 'max' => 36],
			[['name', 'system_id'], 'string', 'max' => 255]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'taxonomy_type_id' => 'Taxonomy Type ID',
			'name' => 'Name',
			'system_id' => 'System ID',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getRelationTaxonomies()
	{
		return $this->hasMany(Taxonomy::className(), ['taxonomy_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getTaxonomyType()
	{
		return $this->hasOne(TaxonomyType::className(), ['id' => 'taxonomy_type_id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getId()
	{
		return $this->hasOne(Registry::className(), ['id' => 'id']);
	}
}
