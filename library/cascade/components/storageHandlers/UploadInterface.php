<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\storageHandlers;

use cascade\models\Storage;

interface UploadInterface
{
    public function handleUpload(Storage $storage, $model, $attribute);
}
