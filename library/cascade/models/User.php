<?php

namespace cascade\models;

use Yii;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;

use yii\helpers\Security;
use infinite\base\exceptions\Exception;

class User extends \infinite\db\models\User
{
    protected $_individual;

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
            if (!$superGroup) { return false; }
            $userClass = self::className();
            $user = new $userClass;
            $user->scenario = 'creation';
            $user->first_name = 'System';
            $user->last_name = 'User';
            $user->username = 'system';
            $user->status = static::STATUS_INACTIVE;
            $user->password =  Security::generateRandomKey();
            $user->relationModels = [['parent_object_id' => $superGroup->primaryKey]];
            if (!$user->save()) {
                throw new Exception("Unable to save system user!");
            }
        }

        return $user;
    }

    /**
     * @return \yii\db\ActiveRelation
     */
    public function getIndividual()
    {
        if (!isset($this->_individual) && !empty($this->object_individual_id)) {
            $this->_individual = false;
            $individualType = Yii::$app->collectors['types']->getOne('Individual');
            if (!empty($individualType->object)) {
                $individualClass = $individualType->object->primaryModel;
                $this->_individual = $individualClass::get($this->object_individual_id);
            }
        }

        return $this->_individual;
    }
}
