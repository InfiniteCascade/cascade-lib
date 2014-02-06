<?php
namespace cascade\components\storageHandlers\core;

use Yii;

use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use infinite\base\exceptions\Exception;
use infinite\helpers\Date;
use cascade\models\Storage;
use cascade\models\StorageEngine;

class LocalHandler extends \cascade\components\storageHandlers\Handler 
	implements \cascade\components\storageHandlers\UploadInterface {
	public $bucketFormat = '{year}.{month}';
	protected $_baseDir;

	public function buildKey()
	{
		$keyVariables = $this->keyVariables;
		$keyParts = explode('.', $this->bucketFormat);
		foreach($keyParts as &$part) {
			$part = strtr($part, $keyVariables);
		}
		return $keyParts;
	}

	public function getKeyVariables()
	{
		$vars = [];
		$time = Date::time();
		$vars['{year}'] = Date::date("Y", $time);
		$vars['{month}'] = Date::date("m", $time);
		$vars['{day}'] = Date::date("d", $time);
		$vars['{hour}'] = Date::date("H", $time);
		$vars['{minute}'] = Date::date("i", $time);
		return $vars;
	}

	public function handleSave(Storage $storage, $model, $attribute)
	{
		return $this->handleUpload($storage, $model, $attribute);
	}

	public function afterDelete(StorageEngine $engine, Storage $model)
	{
		$path = $this->getPath($model);
		if (file_exists($path)) {
			@unlink($path);
		}
		return true;
	}

	public function getPath(Storage $model)
	{
		$baseKey = explode('.', $model->storage_key);
		$dirPath = $this->baseDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $baseKey);
		if (!is_dir($dirPath)) {
			@mkdir($dirPath, 0755, true);
		}
		if (!is_dir($dirPath)) {
			$this->error = 'Unable to create storage directory';
			return false;
		}
		return $dirPath . DIRECTORY_SEPARATOR . $model->primaryKey;
	}

	public function handleUpload(Storage $storage, $model, $attribute)
	{
		$package = [];
		$baseKey = $this->buildKey();
		$package['storage_key'] = implode('.', $baseKey);
		$dirPath = $this->baseDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $baseKey);
		if (!is_dir($dirPath)) {
			@mkdir($dirPath, 0755, true);
		}
		if (!is_dir($dirPath)) {
			$this->error = 'Unable to create storage directory';
			return false;
		}
		$path = $dirPath . DIRECTORY_SEPARATOR . $storage->primaryKey;
		$file = $model->{$attribute};
		if ($file->saveAs($path) && file_exists($path)) {
			$package['file_name'] = $file->baseName;
			$package['size'] = $file->size;
			$package['type'] = FileHelper::getMimeType($path);
			return $package;
		}
		return false;
	}

	public function validate(StorageEngine $engine, $model, $attribute)
	{
		$errorMessage = "No file was uploaded!";
		if ($model->{$attribute} instanceof UploadedFile) {
			if (!$model->{$attribute}->hasError) {
				return true;
			} else {
				$errorMessage = 'An error occurred during file transport.';
			}
		}
		$model->addError($attribute, $errorMessage);
		return false;
	}
	
	public function setBaseDir($value)
	{
		$value = Yii::getAlias($value);
		if (!is_dir($value)) {
			@mkdir($value, 0755, true);
			if (!is_dir($value)) {
				throw new Exception("Unable to set local storage base directory: {$value}");
			}
		}
		return $this->_baseDir = $value;
	}

	public function getBaseDir()
	{
		return $this->_baseDir;
	}

	public function generateInternal($item)
	{
		return $item->fileInput();
	}
}
?>