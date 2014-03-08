<?php

namespace cascade\modules\ThemeCascade\components;

use Yii;

class IdentityAsset extends \cascade\components\web\themes\IdentityAsset
{
    public $sourcePath = '@cascade/modules/ThemeCascade/assets';

	public function getLogoPath()
	{
		if (empty($this->basePath)) { return false; }
		return $this->basePath . DIRECTORY_SEPARATOR . 'logo.png';
	}

}