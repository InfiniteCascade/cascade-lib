<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

/**
 * ObjectTaxonomy is the model class for table "object_taxonomy".
 *
 * @property string $id
 * @property string $object_id
 * @property string $taxonomy_id
 *
 * @property \yii\db\ActiveObject $taxonomy This property is read-only.
 *
 * @property Taxonomy $taxonomy
 * @property Object $object
 *
 * @property \yii\db\ActiveObject $taxonomy This property is read-only.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ObjectTaxonomy extends \cascade\components\db\ActiveRecord
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
        return 'object_taxonomy';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'taxonomy_id'], 'required'],
            [['taxonomy_id', 'object_id'], 'string', 'max' => 36]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'taxonomy_id' => 'Taxonomy ID',
        ];
    }

    /**
     * Get taxonomy
     * @return \yii\db\ActiveObject
     */
    public function getTaxonomy()
    {
        return $this->hasOne(Taxonomy::className(), ['id' => 'taxonomy_id']);
    }

    /**
     * @return \yii\db\ActiveObject
     */
    // public function getObject()
    // {
    // 	return $this->hasOne(Object::className(), ['id' => 'object_id']);
    // }
}
