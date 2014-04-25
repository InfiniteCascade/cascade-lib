<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

/**
 * Roleable [@doctodo write class description for Roleable]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class RelatedObjects extends \infinite\db\behaviors\ActiveRecord
{
    protected $_relatedObjects = [];
    protected $_relations = [];

    public function setRelatedObjects($value)
    {
        $this->_relatedObjects = $value;
    }

    public function getRelatedObjects()
    {
        return $this->_relatedObjects;
    }


    public function setRelations($value)
    {
        $this->_relations = $value;
    }

    public function getRelations()
    {
        return $this->_relations;
    }

    public function safeAttributes()
    {
        return ['relatedObjects', 'relations'];
    }
}
