<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\browser;

use Yii;
use yii\base\InvalidConfigException;
use cascade\components\types\Relationship;
use infinite\helpers\ArrayHelper;

/**
 * HandlerTypes [@doctodo write class description for HandlerTypes]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class HandlerTypes extends \infinite\web\browser\Handler
{
    /**
     * @var __var_bundleClass_type__ __var_bundleClass_description__
     */
    public $bundleClass = 'cascade\components\web\browser\Bundle';

    /**
     * __method_possibleTypes_description__
     * @param __param_topType_type__        $topType   __param_topType_description__
     * @param __param_goodTypes_type__      $goodTypes __param_goodTypes_description__
     * @return __return_possibleTypes_type__ __return_possibleTypes_description__
     */
    public static function possibleTypes($topType, $goodTypes)
    {
        $possibleTypes = [];
        foreach ($topType->collectorItem->children as $relationship) {
            $testType = $relationship->child;
            if ($goodTypes === false || in_array($testType->systemId, $goodTypes)) {
                $possibleTypes[$testType->systemId] = $testType;
            } elseif (self::descendantHas($testType, $goodTypes)) {
                $possibleTypes[$testType->systemId] = $testType;
            }
        }

        return $possibleTypes;
    }

    /**
     * __method_descendantHas_description__
     * @param __param_topType_type__        $topType   __param_topType_description__
     * @param __param_goodTypes_type__      $goodTypes __param_goodTypes_description__
     * @param integer                       $depth     __param_depth_description__ [optional]
     * @return __return_descendantHas_type__ __return_descendantHas_description__
     */
    public static function descendantHas($topType, $goodTypes, $depth = 3)
    {
        $possibleTypes = [];
        foreach ($topType->collectorItem->children as $relationship) {
            $testType = $relationship->child;
            if (in_array($testType->systemId, $goodTypes)) {
                return true;
            } elseif ($depth > 0 && self::descendantHas($testType, $goodTypes, $depth-1)) {
                return true;
            }
        }

        return false;
    }

    /**
    * @inheritdoc
     */
    public function getTotal()
    {
        return count($this->items);
    }

    /**
    * @inheritdoc
     */
    public function getItems()
    {
        $instructions = $this->instructions;
        $items = [];
        if (!isset($instructions['typeFilters'])) {
            $instructions['typeFilters'] = [];
        } elseif (!is_array($instructions['typeFilters'])) {
            $instructions['typeFilters'] = [$instructions['typeFilters']];
        }
        if (isset($instructions['relationshipRole'])) {
            if (!isset($instructions['relationship'])) {
                throw new InvalidConfigException("Relationship type tasks require a relationship ID");
            }
            $relationship = Relationship::getById($instructions['relationship']);
            if (empty($relationship)) {
                throw new InvalidConfigException("Relationship type tasks require a relationship");
            }
            if ($instructions['relationshipRole'] === 'parent') {
                $relationships = $relationship->parent->collectorItem->parents;
                $baseType = $relationship->parent;
            } else {
                $relationships = $relationship->child->collectorItem->parents;
                $baseType = $relationship->child;
            }
            $types = ArrayHelper::map($relationships, 'parent.systemId', 'parent');
            $types[$baseType->systemId] = $baseType;
        } else {
            $typesRaw = Yii::$app->collectors['types']->getAll();
            $types = ArrayHelper::map($typesRaw, 'object.systemId', 'object');
        }
        unset($types['']);
        foreach ($types as $typeKey => $type) {
            if (isset($instructions['limitTypes'])) {
                if (!in_array($typeKey, $instructions['limitTypes'])) {
                    continue;
                }
            }
            if (in_array('hasDashboard', $instructions['typeFilters']) && !$type->hasDashboard) {
                continue;
            }
            if (in_array('authority', $instructions['typeFilters']) && $type->getBehavior('Authority') === null) {
                continue;
            }
            $item = [
                'type' => 'type',
                'id' => $type->systemId,
                'descriptor' => $type->title->upperPlural,
                'hasChildren' => !empty($type->collectorItem->children) || ($instructions['modules'] !== false && in_array($type->systemId, $instructions['modules']))
            ];
            if ($item['hasChildren'] && (!$this->filterQuery || preg_match('/'. preg_quote($this->filterQuery) .'/i', $item['descriptor']) === 1)) {
                $items[] = $item;
            }
        }
        if (!$this->filterQuery) {
            ArrayHelper::multisort($items, 'descriptor', SORT_ASC);
        } else {
            $filterQuery = $this->filterQuery;
            usort($items, function ($a, $b) use ($filterQuery) {
                $a = levenshtein($a['descriptor'], $filterQuery);
                $b = levenshtein($b['descriptor'], $filterQuery);

                return ($a < $b) ? -1 : 1;
            });
        }

        return $items;
    }
}
