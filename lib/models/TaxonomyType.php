<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;
use canis\base\collector\CollectedObjectTrait;

/**
 * TaxonomyType is the model class for table "taxonomy_type".
 *
 * @property string $id
 * @property string $name
 * @property string $system_id
 * @property double $system_version
 * @property string $created
 * @property string $modified
 * @property Taxonomy[] $taxonomies
 * @property Registry $id
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class TaxonomyType extends \cascade\components\db\ActiveRecord implements \canis\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;
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
        return 'taxonomy_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['system_version'], 'number'],
            [['created', 'modified'], 'safe'],
            [['id'], 'string', 'max' => 36],
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
            'name' => 'Name',
            'system_id' => 'System ID',
            'system_version' => 'System Version',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * Get taxonomies.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getTaxonomies()
    {
        return $this->hasMany(Taxonomy::className(), ['taxonomy_type_id' => 'id']);
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
