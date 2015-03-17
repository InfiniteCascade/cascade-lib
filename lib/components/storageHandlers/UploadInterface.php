<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\storageHandlers;

use cascade\models\Storage;

interface UploadInterface
{
    public function handleUpload(Storage $storage, $model, $attribute);
}
