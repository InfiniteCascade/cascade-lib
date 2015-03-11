<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\storageHandlers;

use cascade\models\Storage;

interface UploadInterface
{
    public function handleUpload(Storage $storage, $model, $attribute);
}
