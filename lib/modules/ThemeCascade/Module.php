<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\modules\ThemeCascade;

/**
 * Module [[@doctodo class_description:cascade\modules\ThemeCascade\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\web\themes\Module
{
    /**
     * Get component namespace.
     *
     * @return [[@doctodo return_type:getComponentNamespace]] [[@doctodo return_description:getComponentNamespace]]
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
