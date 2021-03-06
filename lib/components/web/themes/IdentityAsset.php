<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\themes;

/**
 * IdentityAsset [[@doctodo class_description:cascade\components\web\themes\IdentityAsset]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class IdentityAsset extends AssetBundle
{
    /**
     * Get logo path.
     */
    abstract public function getLogoPath();

    /**
     * Get logo.
     *
     * @param [[@doctodo param_type:size]] $size [[@doctodo param_description:size]] [optional]
     *
     * @return [[@doctodo return_type:getLogo]] [[@doctodo return_description:getLogo]]
     */
    public function getLogo($size = null)
    {
        if (!$this->logoPath || !file_exists($this->logoPath)) {
            return;
        }
        $cacheLogo = $this->sizeImageCache($this->logoPath, $size);
        if ($cacheLogo) {
            return $this->getCacheAssetUrl($cacheLogo);
        }

        return false;
    }
}
