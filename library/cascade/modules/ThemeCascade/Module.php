<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\ThemeCascade;

class Module extends \cascade\components\web\themes\Module
{
    public function getComponentNamespace()
    {
        return 'cascade\\modules\\ThemeCascade\\components';
    }

    public function getIdentityAssetBundle()
    {
        return $this->componentNamespace . '\\IdentityAsset';
    }
}
