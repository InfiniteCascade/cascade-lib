<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use infinite\db\Query;
use infinite\db\ActiveQuery;

/**
 * AclRoleQuery [@doctodo write class description for AclRoleQuery]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class AclRoleQuery extends ActiveQuery
{
    /**
    * @inheritdoc
    **/
    public function init()
    {
        parent::init();
        $this->on(Query::EVENT_BEFORE_QUERY, [$this, 'prioritizeNonType']);
    }

    /**
     * __method_prioritizeNonType_description__
     * @param __param_event_type__ $event __param_event_description__ [optional]
     */
    public function prioritizeNonType($event = null)
    {
        $objectTypePrefix = ObjectType::modelPrefix() .'-';
        if (!isset($this->orderBy)) {
            $this->orderBy = [];
        }
        $prioritize = [
            'IF([[controlled_object_id]] LIKE "'.addslashes($objectTypePrefix).'%", 0, 1)' => SORT_DESC
        ];
        $this->orderBy = array_merge($prioritize, $this->orderBy);
    }
}
