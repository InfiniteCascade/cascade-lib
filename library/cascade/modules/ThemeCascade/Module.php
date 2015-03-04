<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\ThemeCascade;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\web\themes\Module
{
    /**
     * Get component namespace
     * @return __return_getComponentNamespace_type__ __return_getComponentNamespace_description__
     */
    public function getComponentNamespace()
    {
        return 'cascade\modules\ThemeCascade\components';
    }

    /**
    * @inheritdoc
     */
    public function getIdentityAssetBundle()
    {
        return $this->componentNamespace . '\IdentityAsset';
    }
}
