<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use Yii;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;

use infinite\base\exceptions\Exception;

/**
 * User is the model class for table "user".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class User extends \infinite\db\models\User
{
    const SYSTEM_EMAIL = 'system@system.local';
    /**
     * @var __var__individual_type__ __var__individual_description__
     */
    protected $_individual;

    use TypesActiveRecordTrait {
        TypesActiveRecordTrait::behaviors as typesBehaviors;
    }

    use BaseActiveRecordTrait {
        BaseActiveRecordTrait::behaviors as baseBehaviors;
    }

    /**
     * @inheritdoc
     */
    public $descriptorField = ['first_name', 'last_name'];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), self::baseBehaviors(), self::typesBehaviors(), [
            'Photo' => 'cascade\components\db\behaviors\Photo',
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['photo_storage_id'], 'safe'],
            [['object_individual_id'], 'string', 'max' => 36],
        ]);
    }

    /**
     * __method_systemUser_description__
     * @return __return_systemUser_type__ __return_systemUser_description__
     * @throws Exception __exception_Exception_description__
     */
    public static function systemUser()
    {
        $user = self::findOne([self::tableName() .'.'. 'email' => self::SYSTEM_EMAIL], false);
        if (empty($user)) {
            $superGroup = Group::find()->disableAccessCheck()->where(['system' => 'super_administrators'])->one();
            if (!$superGroup) { return false; }
            $userClass = self::className();
            $user = new $userClass;
            $user->scenario = 'creation';
            $user->first_name = 'System';
            $user->last_name = 'User';
            $user->email = self::SYSTEM_EMAIL;
            $user->status = static::STATUS_INACTIVE;
            $user->password =  Yii::$app->security->generateRandomKey();
            $user->relationModels = [['parent_object_id' => $superGroup->primaryKey]];
            if (!$user->save()) {
                \d($user->email);
                \d($user->errors);
                throw new Exception("Unable to save system user!");
            }
        }

        return $user;
    }

    /**
     * Get individual
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
    
    /**
     * __method_guessIndividual_description__
     * @return __return_guessIndividual_type__ __return_guessIndividual_description__
     */
    public function guessIndividual()
    {
        $individualTypeItem = Yii::$app->collectors['types']->getOne('Individual');
        $individualClass = $individualTypeItem->object->primaryModel;
        $emailTypeItem = Yii::$app->collectors['types']->getOne('EmailAddress');
        $emailTypeClass = $emailTypeItem->object->primaryModel;
        $emailMatch = $emailTypeClass::find()->where(['email_address' => $this->email])->disableAccessCheck()->all();
        $individuals = [];
        foreach ($emailMatch as $email) {
            if (($individual = $email->parent($individualClass, [], ['disableAccessCheck' => true])) && $individual) {
                $individuals[$individual->primaryKey] = $individual;
            }
        }
        if (empty($individuals)) {
            if (($individualMatch = $individualClass::find()->where(['first_name' => $this->first_name, 'last_name' => $this->last_name])->one()) && $individualMatch) {
                return $individualMatch;
            }
        } else {
            if (count($individuals) === 1) {
                return array_pop($individuals);
            }

            return $individuals;
        }

        return false;
    }
}
