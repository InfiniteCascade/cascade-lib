<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * QueryFamiliarity [@doctodo write class description for QueryFamiliarity]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class QueryFamiliarity extends \infinite\db\behaviors\QueryBehavior
{
    /**
     * __method_withFamiliarity_description__
     * @return \yii\db\ActiveRelation
     */
    public function withFamiliarity()
    {
        if (empty(Yii::$app->user->id)) { return; }
        $familiarityModelClass = Yii::$app->classes['ObjectFamiliarity'];
        $familiartyTable = $familiarityModelClass::tableName();
        $params = [];
        $where = ['and'];
        $where[] = $this->owner->primaryAlias .'.'. $this->owner->primaryTablePk  .' = ft.object_id';
        $where[] = 'ft.user_id = :user_id';
        $params[':user_id'] = Yii::$app->user->id;
        $this->owner->join('LEFT JOIN', $familiartyTable . ' ft', $where, $params);
    }
}
