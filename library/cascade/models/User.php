<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait as BaseActiveRecordTrait;
use cascade\components\types\ActiveRecordTrait as TypesActiveRecordTrait;
use infinite\base\exceptions\Exception;
use Yii;

/**
 * User is the model class for table "user".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class User extends \infinite\db\models\User
{
    /**
     * @var [[@doctodo var_type:_icon]] [[@doctodo var_description:_icon]]
     */
    protected $_icon;
    /**
     * @var [[@doctodo var_type:_profilePhoto]] [[@doctodo var_description:_profilePhoto]]
     */
    protected $_profilePhoto;

    const SYSTEM_EMAIL = 'system@system.local';
    /**
     * @var [[@doctodo var_type:_individual]] [[@doctodo var_description:_individual]]
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['photo_storage_id'], 'safe'],
            [['object_individual_id'], 'string', 'max' => 36],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hasIcon()
    {
        return isset($this->icon) && $this->icon;
    }

    /**
     * @inheritdoc
     */
    public function getIcon($size = 40)
    {
        if (is_null($this->_icon)) {
            $profilePhoto = $this->getPhotoUrl($size);
            if ($profilePhoto) {
                return [
                    'img' => $profilePhoto,
                ];
            }
        }

        return $this->_icon;
    }

    /**
     * [[@doctodo method_description:systemUser]].
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:systemUser]] [[@doctodo return_description:systemUser]]
     *
     */
    public static function systemUser()
    {
        $user = self::findOne([self::tableName() . '.' . 'email' => self::SYSTEM_EMAIL], false);
        if (empty($user)) {
            $superGroup = Group::find()->disableAccessCheck()->where(['system' => 'super_administrators'])->one();
            if (!$superGroup) {
                return false;
            }
            $userClass = self::className();
            $user = new $userClass();
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
     * Get individual.
     *
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
     * Get photo url.
     *
     * @param integer $size [[@doctodo param_description:size]] [optional]
     *
     * @return [[@doctodo return_type:getPhotoUrl]] [[@doctodo return_description:getPhotoUrl]]
     */
    public function getPhotoUrl($size = 200)
    {
        if (!empty($this->individual)
            && $this->individual->getBehavior('Photo') !== null) {
            $indPhoto = $this->individual->getPhotoUrl($size);
            if ($indPhoto) {
                return $indPhoto;
            }
        }
        if ($this->getBehavior('Photo') !== null) {
            return $this->getBehavior('Photo')->getPhotoUrl($size);
        }

        return false;
    }

    /**
     * Get photo email.
     *
     * @return [[@doctodo return_type:getPhotoEmail]] [[@doctodo return_description:getPhotoEmail]]
     */
    public function getPhotoEmail()
    {
        if (!empty($this->email) && substr($this->email, -6) !== ".local") {
            return $this->email;
        }

        return false;
    }

    /**
     * [[@doctodo method_description:guessIndividual]].
     *
     * @return [[@doctodo return_type:guessIndividual]] [[@doctodo return_description:guessIndividual]]
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
