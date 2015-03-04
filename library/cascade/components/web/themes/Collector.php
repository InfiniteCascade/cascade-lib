<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

use Yii;

/**
 * Collector [@doctodo write class description for Collector]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \infinite\base\collector\Module
{
    /**
     * @var __var_initial_type__ __var_initial_description__
     */
    public $initial = [];
    /**
     * @var __var__lastLoadedTheme_type__ __var__lastLoadedTheme_description__
     */
    protected $_lastLoadedTheme;
    /**
     * @var __var__theme_type__ __var__theme_description__
     */
    protected $_theme;

    /**
    * @inheritdoc
     */
    public function getCollectorItemClass()
    {
        return 'cascade\components\web\themes\Item';
    }

    /**
    * @inheritdoc
     */
    public function getModulePrefix()
    {
        return 'Theme';
    }

    /**
     * Get theme
     * @return __return_getTheme_type__ __return_getTheme_description__
     * @throws Exception __exception_Exception_description__
     */
    public function getTheme()
    {
        if (!isset($this->_theme)) {
            return $this->_lastLoadedTheme->object;
        }
        if (!isset($this->_theme)) {
            throw new Exception("No theme has been loaded!");
        }

        return $this->_theme;
    }

    /**
     * __method_registerAssetBundles_description__
     * @param __param_view_type__ $view __param_view_description__
     */
    public function registerAssetBundles($view)
    {
        foreach ($this->theme->assetBundles as $bundle) {
            $bundle::register($view);
        }
    }

    /**
     * Get identity asset bundle
     * @return __return_getIdentityAssetBundle_type__ __return_getIdentityAssetBundle_description__
     */
    public function getIdentityAssetBundle()
    {
        return $this->theme->identityAssetBundle;
    }

    /**
    * @inheritdoc
     */
    public function register($owner, $itemComponent, $systemId = null)
    {
        $item = parent::register($owner, $itemComponent, $systemId);
        $this->_lastLoadedTheme = $item;

        return $item;
    }

    /**
     * Get identity
     * @param __param_view_type__         $view __param_view_description__
     * @return __return_getIdentity_type__ __return_getIdentity_description__
     */
    public function getIdentity($view)
    {
        if (!isset($view->assetBundles[$this->identityAssetBundle])) {
            return false;
        }

        return Yii::$app->assetManager->getBundle($this->identityAssetBundle);
    }
}
