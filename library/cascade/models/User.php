<?php

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;

use yii\helpers\Security;
use infinite\base\exceptions\Exception;

class User extends \infinite\db\models\User
{

	use TypesActiveRecordTrait {
		TypesActiveRecordTrait::behaviors as typesBehaviors;
	}

	use BaseActiveRecordTrait {
		BaseActiveRecordTrait::behaviors as baseBehaviors;
	}
	
	public $descriptorField = ['first_name', 'last_name'];

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), self::baseBehaviors(), self::typesBehaviors(), []);
	}

	public static function systemUser()
	{
		$user = self::findOne([self::tableName() .'.'. 'username' => 'system'], false);
		if (empty($user)) {
			$superGroup = Group::find()->disableAccessCheck()->where(['system' => 'super_administrators'])->one();
			$userClass = self::className();
			$user = new $userClass;
			$user->scenario = 'creation';
			$user->first_name = 'System';
			$user->last_name = 'User';
			$user->username = 'system';
			$user->status = static::STATUS_INACTIVE;
			$user->password =  Security::generateRandomKey();
			if (!$user->save() || !Relation::set($superGroup, $user)) {
				throw new Exception("Unable to save system user!");
			}
		}
		return $user;
	}
}
