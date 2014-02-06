<?php
namespace cascade\components\storageHandlers;

use cascade\models\Storage;

interface UploadInterface {
	public function handleUpload(Storage $storage, $model, $attribute);
}
?>