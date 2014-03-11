<?php
namespace cascade\components\db\behaviors;

use Yii;
use infinite\helpers\ArrayHelper;

class Familiarity extends \infinite\db\behaviors\ActiveRecord
{
	public $familiarityClass = 'cascade\\models\\ObjectFamiliarity';
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
            ObjectFamiliarity::modified($this->owner, $this->user);
        }
    }

    public function afterInsert()
    {
        if ($this->user) {
            ObjectFamiliarity::created($this->owner, $this->user);
        }
    }

    public function getUser($owner = true)
    {
        if ($owner && $this->owner->hasBehavior('Ownable') && isset($this->owner->objectOwner)) {
            return $this->owner->objectOwner;
        } elseif (isset(Yii::$app->user->identity->primaryKey)) {
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
            $familiarityClass = $this->familiarityClass;
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
?>