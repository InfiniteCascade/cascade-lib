<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

/**
 * Taxonomy is the model class for table "taxonomy".
 *
 * @property string $id
 * @property string $taxonomy_type_id
 * @property string $name
 * @property string $system_id
 * @property string $created
 * @property string $modified
 * @property RelationTaxonomy[] $relationTaxonomies
 * @property TaxonomyType $taxonomyType
 * @property Registry $id
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Taxonomy extends \cascade\components\db\ActiveRecord
{
    use ActiveRecordTrait {
        behaviors as baseBehaviors;
    }

    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), self::baseBehaviors(), []);
    }

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
            [['taxonomy_type_id', 'name'], 'required'],
            [['created', 'modified'], 'safe'],
            [['id', 'taxonomy_type_id'], 'string', 'max' => 36],
            [['name', 'system_id'], 'string', 'max' => 255],
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
     * Get relation taxonomies.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getRelationTaxonomies()
    {
        return $this->hasMany(Taxonomy::className(), ['taxonomy_id' => 'id']);
    }

    /**
     * Get taxonomy type.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getTaxonomyType()
    {
        return $this->hasOne(TaxonomyType::className(), ['id' => 'taxonomy_type_id']);
    }

    /**
     * Get id.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getId()
    {
        return $this->hasOne(Registry::className(), ['id' => 'id']);
    }
}
