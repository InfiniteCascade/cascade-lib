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
use yii\helpers\Url;
use cascade\components\helpers\Gravatar;

/**
 * Roleable [@doctodo write class description for Roleable]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Photo extends \cascade\components\storageHandlers\StorageBehavior
{
    public $storageAttribute = 'photo_storage_id';
    public $required = false;

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
        if (empty($photo)) {
            return true;
        }
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

    public function getPhotoUrl($size = 200)
    {
        $storage = $this->storageObject;
        if ($storage) {
            // yay! we have a local image
            return Url::to(['/object/photo', 'id' => $this->owner->primaryKey, 'size' =>  $size]);
        }
        if ($this->owner->photoEmail) {
            $gravatar = new Gravatar;
            if ($gravatar->test($this->owner->photoEmail)) {
                $gravatar->setAvatarSize($size);
            }
            return $gravatar->get($this->owner->photoEmail);
        }
        return false;
    }

    public function getPhotoEmail()
    {
        return false;
    }
}
