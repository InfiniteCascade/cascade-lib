<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * Familiarity [[@doctodo class_description:cascade\components\db\behaviors\Familiarity]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Familiarity extends \infinite\db\behaviors\ActiveRecord
{
    /**
     * @var [[@doctodo var_type:objectField]] [[@doctodo var_description:objectField]]
     */
    public $objectField = 'object_id';
    /**
     * @var [[@doctodo var_type:userField]] [[@doctodo var_description:userField]]
     */
    public $userField = 'user_id';
    /**
     * @var [[@doctodo var_type:_familiarity]] [[@doctodo var_description:_familiarity]]
     */
    protected $_familiarity = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    /**
     * [[@doctodo method_description:afterUpdate]].
     */
    public function afterUpdate()
    {
        if ($this->user) {
            $familiarityClass = Yii::$app->classes['ObjectFamiliarity'];
            $familiarityClass::modified($this->owner, $this->getUser(false));
        }
    }

    /**
     * [[@doctodo method_description:afterInsert]].
     */
    public function afterInsert()
    {
        if ($this->user) {
            $familiarityClass = Yii::$app->classes['ObjectFamiliarity'];
            $familiarityClass::created($this->owner, $this->user);
        }
    }

    /**
     * Get user.
     *
     * @param boolean $owner [[@doctodo param_description:owner]] [optional]
     *
     * @return [[@doctodo return_type:getUser]] [[@doctodo return_description:getUser]]
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
     * [[@doctodo method_description:watch]].
     *
     * @param boolean                      $doWatch [[@doctodo param_description:doWatch]] [optional]
     * @param [[@doctodo param_type:user]] $user    [[@doctodo param_description:user]] [optional]
     *
     * @return [[@doctodo return_type:watch]] [[@doctodo return_description:watch]]
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
     * Get familiarity.
     *
     * @param [[@doctodo param_type:user]] $user [[@doctodo param_description:user]] [optional]
     *
     * @return [[@doctodo return_type:getFamiliarity]] [[@doctodo return_description:getFamiliarity]]
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
                    $this->_familiarity[$familarityKey] = new $familiarityClass();
                    $this->_familiarity[$familarityKey]->attributes = $attributes;
                }
            }
        }

        return $this->_familiarity[$familarityKey];
    }
}
