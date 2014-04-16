<?php
namespace cascade\components\web\themes;

use Yii;

class Collector extends \infinite\base\collector\Module
{
    public $initial = [];
    protected $_lastLoadedTheme;
    protected $_theme;

    public function getCollectorItemClass()
    {
        return 'cascade\\components\\web\\themes\\Item';
    }

    public function getModulePrefix()
    {
        return 'Theme';
    }

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

    public function registerAssetBundles($view)
    {
        foreach ($this->theme->assetBundles as $bundle) {
            $bundle::register($view);
        }
    }

    public function getIdentityAssetBundle()
    {
        return $this->theme->identityAssetBundle;
    }

    public function register($owner, $itemComponent, $systemId = null)
    {
        $item = parent::register($owner, $itemComponent, $systemId);
        $this->_lastLoadedTheme = $item;

        return $item;
    }

    public function getIdentity($view)
    {
        if (!$view->assetBundles[$this->identityAssetBundle]) {
            return false;
        }

        return Yii::$app->assetManager->getBundle($this->identityAssetBundle);
    }
}
