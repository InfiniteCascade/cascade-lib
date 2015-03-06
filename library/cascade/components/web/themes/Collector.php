<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\themes;

use Yii;

/**
 * Collector [[@doctodo class_description:cascade\components\web\themes\Collector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Collector extends \infinite\base\collector\Module
{
    /**
     * @var [[@doctodo var_type:initial]] [[@doctodo var_description:initial]]
     */
    public $initial = [];
    /**
     * @var [[@doctodo var_type:_lastLoadedTheme]] [[@doctodo var_description:_lastLoadedTheme]]
     */
    protected $_lastLoadedTheme;
    /**
     * @var [[@doctodo var_type:_theme]] [[@doctodo var_description:_theme]]
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
     * Get theme.
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:getTheme]] [[@doctodo return_description:getTheme]]
     *
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
     * [[@doctodo method_description:registerAssetBundles]].
     *
     * @param [[@doctodo param_type:view]] $view [[@doctodo param_description:view]]
     */
    public function registerAssetBundles($view)
    {
        foreach ($this->theme->assetBundles as $bundle) {
            $bundle::register($view);
        }
    }

    /**
     * Get identity asset bundle.
     *
     * @return [[@doctodo return_type:getIdentityAssetBundle]] [[@doctodo return_description:getIdentityAssetBundle]]
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
     * Get identity.
     *
     * @param [[@doctodo param_type:view]] $view [[@doctodo param_description:view]]
     *
     * @return [[@doctodo return_type:getIdentity]] [[@doctodo return_description:getIdentity]]
     */
    public function getIdentity($view)
    {
        if (!isset($view->assetBundles[$this->identityAssetBundle])) {
            return false;
        }

        return Yii::$app->assetManager->getBundle($this->identityAssetBundle);
    }
}
