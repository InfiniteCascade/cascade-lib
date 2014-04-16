<?php
namespace cascade\components\web\themes;

abstract class IdentityAsset extends AssetBundle
{
    abstract public function getLogoPath();

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
