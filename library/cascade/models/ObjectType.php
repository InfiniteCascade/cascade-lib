<?php

namespace cascade\models;

use Yii;

/**
 * This is the model class for table "object_type".
 *
 * @property string $name
 * @property double $system_version
 * @property string $created
 * @property string $modified
 */
class ObjectType extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function isAccessControlled()
    {
        return false;
    }

    public function behaviors() {
		return array_merge(parent::behaviors(), [
			'Registry' => [
				'class' => 'infinite\\db\\behaviors\\Registry'
			],
			'Roleable' => [
				'class' => 'cascade\\components\\db\\behaviors\\Roleable',
			],
			'ActiveAccess' => [
				'class' => 'cascade\\components\\db\\behaviors\\ActiveAccess',
			],
		]);
	}

	public function determineAccessLevel($role, $aro = null)
    {
    	$objectTypeItem = Yii::$app->collectors['types']->getOne($this->name);
    	if ($objectTypeItem && ($objectType = $objectTypeItem->object) && $objectType) {
    		return $objectType->determineAccessLevel(null, $role, $aro);
    	}
        return false;
    }
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_type';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id'], 'string', 'max' => 36],
			[['name'], 'required'],
			[['system_version'], 'number'],
			[['created', 'modified'], 'safe'],
			[['name'], 'string', 'max' => 255]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'name' => 'Name',
			'system_version' => 'System Version',
			'created' => 'Created',
			'modified' => 'Modified',
		];
	}
}
