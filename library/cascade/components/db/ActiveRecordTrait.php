<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db;

use cascade\models\Relation;
use cascade\models\Registry;

trait ActiveRecordTrait
{
    public $_moduleHandler;

    static protected $_fields = [];
    protected $_defaultOrder = '{alias}.name ASC';

    public static function getRegistryClass()
    {
        return Registry::className();
    }

    public static function getRelationClass()
    {
        return Relation::className();
    }

    public function getTabularId()
    {
        if (is_null($this->_tabularId)) {
            if (is_null($this->_moduleHandler) || $this->_moduleHandler === self::FORM_PRIMARY_MODEL) {
                //$this->_moduleHandler = self::FORM_PRIMARY_MODEL;
                $this->_tabularId = self::getPrimaryTabularId();
            } else {
                $this->_tabularId = self::generateTabularId($this->_moduleHandler);
            }
        }

        return $this->_tabularId;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return array_merge($behaviors, []);
    }
}
