<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * QueryFamiliarity [[@doctodo class_description:cascade\components\db\behaviors\QueryFamiliarity]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class QueryFamiliarity extends \canis\db\behaviors\QueryBehavior
{
    /**
     * [[@doctodo method_description:withFamiliarity]].
     *
     * @return \yii\db\ActiveRelation
     */
    public function withFamiliarity()
    {
        if (empty(Yii::$app->user->id)) {
            return;
        }
        $familiarityModelClass = Yii::$app->classes['ObjectFamiliarity'];
        $familiartyTable = $familiarityModelClass::tableName();
        $params = [];
        $where = ['and'];
        $where[] = $this->owner->primaryAlias . '.' . $this->owner->primaryTablePk . ' = ft.object_id';
        $where[] = 'ft.user_id = :user_id';
        $params[':user_id'] = Yii::$app->user->id;
        $this->owner->join('LEFT JOIN', $familiartyTable . ' ft', $where, $params);
    }
}
