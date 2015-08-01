<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\storageHandlers;

use cascade\models\Storage;

interface UploadInterface
{
    public function handleUpload(Storage $storage, $model, $attribute);
}
