<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * Search [@doctodo write class description for Search]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Search extends \infinite\base\Component
{
    /**
     * @var __var_interactive_type__ __var_interactive_description__
     */
    public static $interactive = true;
    /**
     * @var __var_threshold_type__ __var_threshold_description__
     */
    public $threshold = 9;
    /**
     * @var __var_autoadjust_type__ __var_autoadjust_description__
     */
    public $autoadjust = 1.5;
    /**
     * @var __var_dataSource_type__ __var_dataSource_description__
     */
    public $dataSource;

    /**
     * @var __var__localFields_type__ __var__localFields_description__
     */
    protected $_localFields;
    /**
     * @var __var__foreignFields_type__ __var__foreignFields_description__
     */
    protected $_foreignFields = [];

    /**
     * __method_searchLocal_description__
     * @param cascade\components\dataInterface\DataItem $item         __param_item_description__
     * @param array                                     $searchParams __param_searchParams_description__ [optional]
     * @return __return_searchLocal_type__               __return_searchLocal_description__
     */
    public function searchLocal(DataItem $item, $searchParams = [])
    {
        if (!isset($searchParams['searchFields'])) {
            $searchParams['searchFields'] = $this->localFields;
        }
        if (!isset($searchParams['limit'])) {
            $searchParams['limit'] = 5;
        }
        $query = [];
        foreach ($this->foreignFields as $field) {
            if (!empty($item->foreignObject->{$field})) {
                $query[] = $item->foreignObject->{$field};
            }
        }

        if (empty($query)) {
            return false;
        }

        $localClass = $this->dataSource->localModel;
        $searchResults = $localClass::searchTerm(implode(' ', $query), $searchParams);
        foreach ($searchResults as $k => $r) {
            if ($r->score < $this->threshold) {
                unset($searchResults[$k]);
            } else {
                $reverseKey = $this->dataSource->getReverseKeyTranslation($r->id);
                if (!empty($reverseKey)) {
                    unset($searchResults[$k]);
                }
            }
        }

        $searchResults = array_values($searchResults);
        if (empty($searchResults)) {
            return false;
        } elseif (count($searchResults) === 1
            || !self::$interactive
            || $searchResults[0]->score > ($this->threshold * $this->autoadjust)) {
            return $searchResults[0]->object;
        } else {
            \d($query);
            \d($searchResults);exit;
        }
    }

    /**
     * __method_searchForeign_description__
     * @param cascade\components\dataInterface\DataItem $item __param_item_description__
     * @return __return_searchForeign_type__             __return_searchForeign_description__
     */
    public function searchForeign(DataItem $item)
    {
        return false;
    }

    /**
     * __method_setLocalFields_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setLocalFields($value)
    {
        $this->_localFields = $value;
    }

    /**
     * __method_getLocalFields_description__
     * @return __return_getLocalFields_type__ __return_getLocalFields_description__
     */
    public function getLocalFields()
    {
        return $this->_localFields;
    }

    /**
     * __method_setForeignFields_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setForeignFields($value)
    {
        $this->_foreignFields = $value;
    }

    /**
     * __method_getForeignFields_description__
     * @return __return_getForeignFields_type__ __return_getForeignFields_description__
     */
    public function getForeignFields()
    {
        return $this->_foreignFields;
    }

    /**
     * __method_getModule_description__
     * @return __return_getModule_type__ __return_getModule_description__
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }
}
