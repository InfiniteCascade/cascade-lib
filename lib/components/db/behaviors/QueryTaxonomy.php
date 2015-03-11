<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * QueryTaxonomy [[@doctodo class_description:cascade\components\db\behaviors\QueryTaxonomy]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class QueryTaxonomy extends \teal\db\behaviors\ActiveRecord
{
    /**
     * @var [[@doctodo var_type:viaModelClass]] [[@doctodo var_description:viaModelClass]]
     */
    public $viaModelClass = 'ObjectTaxonomy';
    /**
     * @var [[@doctodo var_type:relationKey]] [[@doctodo var_description:relationKey]]
     */
    public $relationKey = 'object_id';
    /**
     * @var [[@doctodo var_type:taxonomyKey]] [[@doctodo var_description:taxonomyKey]]
     */
    public $taxonomyKey = 'taxonomy_id';

    /**
     * [[@doctodo method_description:filterByTaxonomy]].
     *
     * @param [[@doctodo param_type:value]] $value  [[@doctodo param_description:value]]
     * @param array                         $params [[@doctodo param_description:params]] [optional]
     */
    public function filterByTaxonomy($value, $params = [])
    {
        $queryAlias = isset($params['queryAlias']) ? $params['queryAlias'] : $this->owner->primaryAlias;
        $queryPk = isset($params['queryPk']) ? $params['queryPk'] : $this->owner->primaryTablePk;
        $taxonomyAlias = isset($params['taxonomyAlias']) ? $params['taxonomyAlias'] : 'tax';

        $pivotTableClass = Yii::$app->classes[$this->viaModelClass];
        $pivotTable = $pivotTableClass::tableName();
        $taxonomy = static::parseTaxonomyValue($value);
        $params = [];
        $this->owner->andWhere(['{{' . $taxonomyAlias . '}}.[[' . $this->taxonomyKey . ']]' => $taxonomy]);
        $conditions = ['and', '{{' . $queryAlias . '}}.[[' . $queryPk . ']]={{' . $taxonomyAlias . '}}.[[' . $this->relationKey . ']]'];
        $this->owner->leftJoin([$taxonomyAlias => $pivotTable], $conditions, $params);
    }

    /**
     * [[@doctodo method_description:parseTaxonomyValue]].
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @return [[@doctodo return_type:parseTaxonomyValue]] [[@doctodo return_description:parseTaxonomyValue]]
     */
    public static function parseTaxonomyValue($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $k => $v) {
            if (is_object($v)) {
                $value[$k] = $v->primaryKey;
            } elseif (is_array($v)) {
                unset($value[$k]);
                if (isset($v['systemId']) && isset($v['taxonomyType'])) {
                    $taxonomyType = Yii::$app->collectors['taxonomies']->getOne($v['taxonomyType']);
                    if (isset($taxonomyType) && ($taxonomy = $taxonomyType->getTaxonomy($v['systemId']))) {
                        $value[$k] = $taxonomy->primaryKey;
                    }
                }
            }
        }
        if (empty($value)) {
            $value = [0];
        }

        return $value;
    }
}
