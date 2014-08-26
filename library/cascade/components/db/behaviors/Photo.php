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
use yii\imagine\Image;
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

    public function badFields()
    {
    	return [$this->storageAttribute];
    }

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
                return $gravatar->get($this->owner->photoEmail);
            }
        }
        return false;
    }

    public function serve($options = [])
    {
        if (empty($options)) {
            return parent::serve();
        }
        $path = $this->storagePath;
        if (!$path || !file_exists($path)) {
            return false;
        }
        $imagine = Image::getImagine();
        if (!$imagine) {
            return false;
        }
        $cacheKey = ['photo', $path, $options];
        $content = Yii::$app->fileCache->get($cacheKey);
        if (!$content) {
            if (!($image = $imagine->open($path))) {
                return false;
            }
            if (isset($options['rotate'])) {
                $image->rotate($options['rotate']);
            }
            $currentSize = $size = $image->getSize();
            if (isset($options['width']) || isset($options['height'])) {
                if (!isset($options['width'])) {
                    $currentWidth = (int)$currentSize->getWidth();
                    $newHeight = (int)$options['height'];
                    $oldHeight = (int)$currentSize->getHeight();
                    $options['width'] = $currentWidth * ($newHeight/$oldHeight);
                }
                if (!isset($options['height'])) {
                    $currentHeight = (int)$currentSize->getHeight();
                    $newWidth = (int)$options['width'];
                    $oldWidth = (int)$currentSize->getWidth();
                    $options['height'] = $currentHeight * ($newWidth/$oldWidth);
                }
                $size = new \Imagine\Image\Box((int)$options['width'], (int)$options['height']);
            }
            $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
            $image = $image->thumbnail($size, $mode);
            $newSize = $image->getSize();
            if ($newSize->getWidth() < $size->getWidth()
                || $newSize->getHeight() < $size->getHeight()) {
                $color = null;
                // $palette = new \Imagine\Image\Palette\RGB;
                // $color = $palette->color('#fff', 100);
                $frameImage = $imagine->create($size, $color);
                $widthCoord = ($size->getWidth() / 2) - ($newSize->getWidth() / 2);
                $heightCoord = ($size->getHeight() / 2) - ($newSize->getHeight() / 2);
                $coord = new \Imagine\Image\Point($widthCoord, $heightCoord);
                $frameImage->paste($image, $coord);
                $image = $frameImage;
            }
            $content = $image->get('png');
            Yii::$app->fileCache->set($cacheKey, $content, 3600, new \yii\caching\FileDependency(['fileName' => $path]));
        }
       
        if (!$content) {
            return false;
        }
        Yii::$app->response->sendContentAsFile($content, trim($this->storageObject->file_name), 'image/png');
        return true;
    }

    public function getPhotoEmail()
    {
        return false;
    }
}
