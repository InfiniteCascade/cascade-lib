<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

use yii\imagine\Image;

/**
 * AssetBundle [@doctodo write class description for AssetBundle]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class AssetBundle extends \yii\web\AssetBundle
{
    public function sizeImageCache($imagePath, $size)
    {
        $sizeKey = md5(json_encode($size));
        if (!($cachePath = $this->cachePath)) {
            return false;
        }
        if (empty($size)) {
            return $imagePath;
        }
        $filename = pathinfo($imagePath);
        $cachePath = $cachePath . DIRECTORY_SEPARATOR . $filename['filename'] . '_'. $sizeKey .'.'. $filename['extension'];
        if (file_exists($cachePath)) {
            return $cachePath;
        }
        $image = $this->followResizeInstructions($imagePath, $size);
        if (!$image) { return false; }
        $image->save($cachePath);
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        return false;
    }

    protected function followResizeInstructions($imagePath, $resize)
    {
        if (is_object($imagePath)) {
            $image = $imagePath;
        } else {
            $imagine = Image::getImagine();
            $image = $imagine->open($imagePath);
        }
        if (!$image) { return false; }
        $size = $image->getSize();
        if (isset($resize['width']) && $resize['width'] < $size->getWidth()) {
            $image->resize($size->widen($resize['width']));
        }
        if (isset($resize['height']) && $resize['height'] < $size->getHeight()) {
            $image->resize($size->heighten($resize['height']));
        }

        return $image;
    }

    public function getCachePath()
    {
        if (empty($this->basePath)) { return false; }
        $cachePath = $this->basePath . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($cachePath)) {
            @mkdir($cachePath, 0777, true);
        }
        if (!is_dir($cachePath)) {
            return false;
        }

        return $cachePath;
    }

    public function getCacheUrl()
    {
        if (empty($this->baseUrl)) { return false; }
        $cacheUrl = $this->baseUrl . '/cache';

        return $cacheUrl;
    }

    public function getCacheAssetUrl($path)
    {
        $url = $this->cacheUrl .'/'. basename($path);

        return $url;
    }
}
