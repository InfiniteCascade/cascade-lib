<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\models;

/**
 * RelationTaxonomy is the model class for table "relation_taxonomy".
 *
 * @property string $id
 * @property string $relation_id
 * @property string $taxonomy_id
 * @property Taxonomy $taxonomy
 * @property Relation $relation
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelationTaxonomy extends \cascade\components\db\ActiveRecord
{
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
            [['taxonomy_id'], 'string', 'max' => 36],
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
     * Get taxonomy.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getTaxonomy()
    {
        return $this->hasOne(Taxonomy::className(), ['id' => 'taxonomy_id']);
    }
}
