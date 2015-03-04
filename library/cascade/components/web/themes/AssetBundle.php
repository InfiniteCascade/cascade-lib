<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

use yii\imagine\Image;

/**
 * AssetBundle [@doctodo write class description for AssetBundle].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AssetBundle extends \yii\web\AssetBundle
{
    /**
     * __method_sizeImageCache_description__.
     *
     * @param __param_imagePath_type__ $imagePath __param_imagePath_description__
     * @param __param_size_type__      $size      __param_size_description__
     *
     * @return __return_sizeImageCache_type__ __return_sizeImageCache_description__
     */
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
        $cachePath = $cachePath . DIRECTORY_SEPARATOR . $filename['filename'] . '_' . $sizeKey . '.' . $filename['extension'];
        if (file_exists($cachePath)) {
            return $cachePath;
        }
        $image = $this->followResizeInstructions($imagePath, $size);
        if (!$image) {
            return false;
        }
        $image->save($cachePath);
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        return false;
    }

    /**
     * __method_followResizeInstructions_description__.
     *
     * @param __param_imagePath_type__ $imagePath __param_imagePath_description__
     * @param __param_resize_type__    $resize    __param_resize_description__
     *
     * @return __return_followResizeInstructions_type__ __return_followResizeInstructions_description__
     */
    protected function followResizeInstructions($imagePath, $resize)
    {
        if (is_object($imagePath)) {
            $image = $imagePath;
        } else {
            $imagine = Image::getImagine();
            $image = $imagine->open($imagePath);
        }
        if (!$image) {
            return false;
        }
        $size = $image->getSize();
        if (isset($resize['width']) && $resize['width'] < $size->getWidth()) {
            $image->resize($size->widen($resize['width']));
        }
        if (isset($resize['height']) && $resize['height'] < $size->getHeight()) {
            $image->resize($size->heighten($resize['height']));
        }

        return $image;
    }

    /**
     * Get cache path.
     *
     * @return __return_getCachePath_type__ __return_getCachePath_description__
     */
    public function getCachePath()
    {
        if (empty($this->basePath)) {
            return false;
        }
        $cachePath = $this->basePath . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($cachePath)) {
            @mkdir($cachePath, 0777, true);
        }
        if (!is_dir($cachePath)) {
            return false;
        }

        return $cachePath;
    }

    /**
     * Get cache url.
     *
     * @return __return_getCacheUrl_type__ __return_getCacheUrl_description__
     */
    public function getCacheUrl()
    {
        if (empty($this->baseUrl)) {
            return false;
        }
        $cacheUrl = $this->baseUrl . '/cache';

        return $cacheUrl;
    }

    /**
     * Get cache asset url.
     *
     * @param __param_path_type__ $path __param_path_description__
     *
     * @return __return_getCacheAssetUrl_type__ __return_getCacheAssetUrl_description__
     */
    public function getCacheAssetUrl($path)
    {
        $url = $this->cacheUrl . '/' . basename($path);

        return $url;
    }
}
