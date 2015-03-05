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
 * AssetBundle [[@doctodo class_description:cascade\components\web\themes\AssetBundle]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AssetBundle extends \yii\web\AssetBundle
{
    /**
     * [[@doctodo method_description:sizeImageCache]].
     *
     * @return [[@doctodo return_type:sizeImageCache]] [[@doctodo return_description:sizeImageCache]]
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
     * [[@doctodo method_description:followResizeInstructions]].
     *
     * @return [[@doctodo return_type:followResizeInstructions]] [[@doctodo return_description:followResizeInstructions]]
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
     * @return [[@doctodo return_type:getCachePath]] [[@doctodo return_description:getCachePath]]
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
     * @return [[@doctodo return_type:getCacheUrl]] [[@doctodo return_description:getCacheUrl]]
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
     * @return [[@doctodo return_type:getCacheAssetUrl]] [[@doctodo return_description:getCacheAssetUrl]]
     */
    public function getCacheAssetUrl($path)
    {
        $url = $this->cacheUrl . '/' . basename($path);

        return $url;
    }
}
