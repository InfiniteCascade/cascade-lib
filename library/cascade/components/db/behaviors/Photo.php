<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;
use infinite\base\FileInterface;
use infinite\base\File;
use infinite\base\RawFile;

/**
 * Roleable [@doctodo write class description for Roleable]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Photo extends \cascade\components\storageHandlers\StorageBehavior
{
    public $storageAttribute = 'photo_storage_id';

    public function safeAttributes()
    {
        return ['rawPhoto', 'photo'];
    }

    public function setPhoto($photo)
    {
        return $this->setStorage($photo);
    }

    public function setRawPhoto($photo)
    {
        if (!($photo instanceof FileInterface)) {
            $photo = RawFile::createRawInstance($photo);
        }
        return $this->setStorage($photo);
    }

    public function getRawPhoto()
    {
        return $this->getStorage();
    }

    public function getPhoto()
    {
        return $this->getStorage();
    }

    public function beforeValidate($event)
    {
        if (!parent::beforeValidate($event)) {
            return false;
        }
        return true;
    }
}
