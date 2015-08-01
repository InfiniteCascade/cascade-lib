<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use canis\db\ActiveQuery;
use canis\db\Query;

/**
 * AclRoleQuery [[@doctodo class_description:cascade\models\AclRoleQuery]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AclRoleQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(Query::EVENT_BEFORE_QUERY, [$this, 'prioritizeNonType']);
    }

    /**
     * [[@doctodo method_description:prioritizeNonType]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]] [optional]
     */
    public function prioritizeNonType($event = null)
    {
        $objectTypePrefix = ObjectType::modelPrefix() . '-';
        if (!isset($this->orderBy)) {
            $this->orderBy = [];
        }
        $prioritize = [
            'IF([[controlled_object_id]] LIKE "' . addslashes($objectTypePrefix) . '%", 0, 1)' => SORT_DESC,
        ];
        $this->orderBy = array_merge($prioritize, $this->orderBy);
    }
}
