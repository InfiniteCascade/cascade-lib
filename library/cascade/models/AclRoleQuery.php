<?php
namespace cascade\models;

use infinite\db\Query;
use infinite\db\ActiveQuery;

class AclRoleQuery extends ActiveQuery
{
    public function init()
    {
        parent::init();
        $this->on(Query::EVENT_BEFORE_QUERY, [$this, 'prioritizeNonType']);
    }

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
