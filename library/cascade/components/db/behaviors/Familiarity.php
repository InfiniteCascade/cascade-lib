<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

class Familiarity extends \infinite\db\behaviors\ActiveRecord
{
    public $objectField = 'object_id';
    public $userField = 'user_id';
    protected $_familiarity = [];

    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    public function afterUpdate()
    {
        if ($this->user) {
            $familiarityClass = Yii::$app->classes['ObjectFamiliarity'];
            $familiarityClass::modified($this->owner, $this->getUser(false));
        }
    }

    public function afterInsert()
    {
        if ($this->user) {
            $familiarityClass = Yii::$app->classes['ObjectFamiliarity'];
            $familiarityClass::created($this->owner, $this->user);
        }
    }

    public function getUser($owner = true)
    {
        if ($owner && $this->owner->getBehavior('Ownable') !== null && isset($this->owner->objectOwner)) {
            return $this->owner->objectOwner;
        } elseif (isset(Yii::$app->user) && isset(Yii::$app->user->identity->primaryKey)) {
            return Yii::$app->user->identity;
        }

        return false;
    }

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
