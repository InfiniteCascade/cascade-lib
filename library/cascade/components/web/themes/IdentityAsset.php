<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

/**
 * IdentityAsset [@doctodo write class description for IdentityAsset]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
abstract class IdentityAsset extends AssetBundle
{
    /**
     * __method_getLogoPath_description__
     */
    abstract public function getLogoPath();

    /**
     * __method_getLogo_description__
     * @param  __param_size_type__     $size __param_size_description__ [optional]
     * @return __return_getLogo_type__ __return_getLogo_description__
     */
    public function getLogo($size = null)
    {
        if (!$this->logoPath || !file_exists($this->logoPath)) { return; }
        $cacheLogo = $this->sizeImageCache($this->logoPath, $size);
        if ($cacheLogo) {
            return $this->getCacheAssetUrl($cacheLogo);
        }

        return false;
    }
}
