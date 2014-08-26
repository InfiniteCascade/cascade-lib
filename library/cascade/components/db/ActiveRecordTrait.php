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

    protected $_defaultOrder = '{alias}.name ASC';

    public static function getRegistryClass()
    {
        return Registry::className();
    }

    public static function getRelationClass()
    {
        return Relation::className();
    }
    
    public function badFields()
    {
    	$badFields = parent::badFields();
    	$badFields[] = 'archived';
    	foreach ($this->getBehaviors() as $behavior) {
    		if (method_exists($behavior, 'badFields')) {
    			$badFields = array_merge($badFields, $behavior->badFields());
    		}
    	}
    	foreach ($this->attributes() as $attr) {
    		if (preg_match('/\_user\_id$/', $attr) === 1) {
    			$badFields[] = $attr;
    		}
    	}
    	return array_unique($badFields);
    }
    
    public function getTabularId()
    {
        if (is_null($this->_tabularId)) {
            if (is_null($this->_moduleHandler) || $this->_moduleHandler === self::FORM_PRIMARY_MODEL) {
                //return false;
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
