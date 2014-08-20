<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * Roleable [@doctodo write class description for Roleable]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Photo extends \infinite\db\behaviors\ActiveRecord
{
    public $photoStorageField = 'photo_storage_id';
    
    public function events()
    {
        return [
            \infinite\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            \infinite\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            \infinite\db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
        ];
    }
    public function beforeSave($event)
    {
        
    }

    public function afterSave($event)
    {
        
    }

    public function beforeValidate($event)
    {
        
    }

    public function safeAttributes()
    {
        return ['photo'];
    }
}
