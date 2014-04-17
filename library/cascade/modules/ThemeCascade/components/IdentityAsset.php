<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\ThemeCascade\components;

/**
 * IdentityAsset [@doctodo write class description for IdentityAsset]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class IdentityAsset extends \cascade\components\web\themes\IdentityAsset
{
    public $sourcePath = '@cascade/modules/ThemeCascade/assets';

    /**
    * @inheritdoc
    **/
    public function getLogoPath()
    {
        if (empty($this->basePath)) { return false; }

        return $this->basePath . DIRECTORY_SEPARATOR . 'logo.png';
    }

}
