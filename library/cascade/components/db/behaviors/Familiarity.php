<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * Familiarity [@doctodo write class description for Familiarity]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Familiarity extends \infinite\db\behaviors\ActiveRecord
{
    /**
     * @var __var_objectField_type__ __var_objectField_description__
     */
    public $objectField = 'object_id';
    /**
     * @var __var_userField_type__ __var_userField_description__
     */
    public $userField = 'user_id';
    /**
     * @var __var__familiarity_type__ __var__familiarity_description__
     */
    protected $_familiarity = [];

    /**
    * @inheritdoc
     */
    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    /**
     * __method_afterUpdate_description__
     */
    public function afterUpdate()
    {
        if ($this->user) {
            $familiarityClass = Yii::$app->classes['ObjectFamiliarity'];
            $familiarityClass::modified($this->owner, $this->getUser(false));
        }
    }

    /**
     * __method_afterInsert_description__
     */
    public function afterInsert()
    {
        if ($this->user) {
            $familiarityClass = Yii::$app->classes['ObjectFamiliarity'];
            $familiarityClass::created($this->owner, $this->user);
        }
    }

    /**
     * Get user
     * @param boolean                 $owner __param_owner_description__ [optional]
     * @return __return_getUser_type__ __return_getUser_description__
     */
    public function getUser($owner = true)
    {
        if ($owner && $this->owner->getBehavior('Ownable') !== null && isset($this->owner->objectOwner)) {
            return $this->owner->objectOwner;
        } elseif (isset(Yii::$app->user) && isset(Yii::$app->user->identity->primaryKey)) {
            return Yii::$app->user->identity;
        }

        return false;
    }

    /**
     * __method_watch_description__
     * @param boolean               $doWatch __param_doWatch_description__ [optional]
     * @param __param_user_type__   $user    __param_user_description__ [optional]
     * @return __return_watch_type__ __return_watch_description__
     */
    public function watch($doWatch = true, $user = null)
    {
        $familiarity = $this->getFamiliarity($user);
        if (!$familiarity) {
            return false;
        }
        if ($doWatch) {
            $familiarity->watching = 1;
        } else {
            $familiarity->watching = 0;
        }

        return $familiarity->save();
    }

    /**
     * Get familiarity
     * @param __param_user_type__            $user __param_user_description__ [optional]
     * @return __return_getFamiliarity_type__ __return_getFamiliarity_description__
     */
    public function getFamiliarity($user = null)
    {
        if (is_null($user)) {
            $user = $this->getUser(false);
        }
        if (is_object($user)) {
            $user = $user->primaryKey;
        }
        $familarityKey = md5($user);
        if (!isset($this->_familiarity[$familarityKey])) {
            $this->_familiarity[$familarityKey] = false;
            $familiarityClass = Yii::$app->classes['ObjectFamiliarity'];
            if (!empty($user)) {
                $attributes = [];
                $attributes[$this->objectField] = $this->owner->primaryKey;
                $attributes[$this->userField] = $user;
                $this->_familiarity[$familarityKey] = $familiarityClass::find()->where($attributes)->one();
                if (empty($this->_familiarity[$familarityKey])) {
                    $this->_familiarity[$familarityKey] = new $familiarityClass;
                    $this->_familiarity[$familarityKey]->attributes = $attributes;
                }
            }
        }

        return $this->_familiarity[$familarityKey];
    }
}
