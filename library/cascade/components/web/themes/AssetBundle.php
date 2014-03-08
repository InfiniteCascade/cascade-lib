<?php
namespace cascade\components\web\themes;

use yii\imagine\Image;
use Imagine\Image\Box;

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
		$imagine = Imagine::getImagine();
		return $cachePath;
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
}
