<?php

namespace cascade\models;

/**
 * This is the model class for table "http_session".
 *
 * @property string $id
 * @property integer $expire
 * @property string $data
 */
class HttpSession extends \cascade\components\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'http_session';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id'], 'required'],
			[['expire'], 'integer'],
			[['data'], 'string'],
			[['id'], 'string', 'max' => 255]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'expire' => 'Expire',
			'data' => 'Data',
		];
	}
}
