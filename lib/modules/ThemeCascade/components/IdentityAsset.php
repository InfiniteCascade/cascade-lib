<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\modules\ThemeCascade\components;

/**
 * IdentityAsset [[@doctodo class_description:cascade\modules\ThemeCascade\components\IdentityAsset]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class IdentityAsset extends \cascade\components\web\themes\IdentityAsset
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@cascade/modules/ThemeCascade/assets';

    /**
     * @inheritdoc
     */
    public function getLogoPath()
    {
        if (empty($this->basePath)) {
            return false;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . 'logo.png';
    }
}
