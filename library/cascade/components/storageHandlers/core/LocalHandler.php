<?php
namespace cascade\components\storageHandlers\core;

use Yii;
use infinite\base\exceptions\Exception;

class LocalHandler extends \cascade\components\storageHandlers\Handler {
	public $bucketFormat = '{year}.{month}';
	protected $_baseDir;

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
}
?>