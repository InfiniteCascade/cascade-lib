<?php
namespace cascade\components\web\themes;

abstract class IdentityAsset extends AssetBundle
{
	abstract public function getLogoPath();

	public function getLogo($size = null)
	{
		if (!$this->logoPath || !file_exists($this->logoPath)) { return; }

		exit;
		return 'boom';
	}
}
