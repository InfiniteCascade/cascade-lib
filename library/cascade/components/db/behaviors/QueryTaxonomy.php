<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use Yii;

/**
 * Taxonomy [@doctodo write class description for Taxonomy].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class QueryTaxonomy extends \infinite\db\behaviors\ActiveRecord
{
    /**
     */
    public $viaModelClass = 'ObjectTaxonomy';
    /**
     */
    public $relationKey = 'object_id';
    /**
     */
    public $taxonomyKey = 'taxonomy_id';

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
