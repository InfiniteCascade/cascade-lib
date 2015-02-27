<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use infinite\helpers\Console;
use cascade\components\helpers\StringHelper;
use infinite\helpers\ArrayHelper;

/**
 * Search [@doctodo write class description for Search]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Search extends \infinite\base\Component
{
    /**
     * @var __var_interactive_type__ __var_interactive_description__
     */
    public static $interactive = true;
    /**
     * @var __var_threshold_type__ __var_threshold_description__
     */
    public $threshold = 50;
    /**
     * @var __var_autoadjust_type__ __var_autoadjust_description__
     */
    public $autoadjust = 1.5;
    /**
     * @var __var_dataSource_type__ __var_dataSource_description__
     */
    public $dataSource;

    public $foreignFilter;

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
        if (empty($searchParams['searchFields'])) {
            $searchParams['searchFields'] = $this->localFields;
        }
        if (empty($searchParams['searchFields'])) {
            return null;
        }
        if (!isset($searchParams['limit'])) {
            $searchParams['limit'] = 5;
        }
        $searchParams['skipForeign'] = true;
        $query = [];
        foreach ($this->foreignFields as $field) {
            if (!empty($item->foreignObject->{$field})) {
                $value = $item->foreignObject->{$field};
                if (isset($this->foreignFilter)) {
                    $value = call_user_func($this->foreignFilter, $value);
                }
                $query[] = $value;
            }
        }
        if (empty($query)) {
            return null;
        }

        $localClass = $this->dataSource->localModel;
        $searchResults = $localClass::searchTerm(implode(' ', $query), $searchParams);
        foreach ($searchResults as $k => $r) {
            // if ($r->descriptor === $query[0]) { continue; }
            $score = (
                (($r->score * 100) * .2)
                + (StringHelper::compareStrings($r->descriptor, implode(' ', $query)) * .8)
                );
            $r->score = $score;
            if ($score < $this->threshold) {
                unset($searchResults[$k]);
            } else {
                $reverseKey = $this->dataSource->getReverseKeyTranslation($r->id);
                if (!empty($reverseKey)) {
                    unset($searchResults[$k]);
                }
            }
        }
        ArrayHelper::multisort($searchResults, 'scoreSort', SORT_DESC);
        $searchResults = array_values($searchResults);

        if (empty($searchResults)) {
            return null;
        } elseif (
            count($searchResults) === 1
            || !self::$interactive
            ||  (
                    $searchResults[0]->score > ($this->threshold * $this->autoadjust)
                    && (!isset($searchResults[1]) || $searchResults[0]->score !== $searchResults[1]->score)
                )
            || (
                    $searchResults[0]->descriptor === implode(' ', $query)
                    && (!isset($searchResults[1]) || $searchResults[1]->descriptor !== implode(' ', $query))
                )
        ) {
            // if (!$item->ignoreForeignObject) {
            //     \d(count($searchResults), $searchResults[1]->descriptor);exit;
            // }
            return $searchResults[0]->object;
        } else {
            $options = [];
            $resultsNice = [];
            $optionNumber = 1;
            foreach ($searchResults as $result) {
                $resultsNice['_o' . $optionNumber] = $result;
                $options['_o' . $optionNumber] = $result->descriptor .' ('. $result->score .'%)';
                $optionNumber++;
            }
            $options['new'] = 'Create New Object';
            $select = false;
            $interactionOptions = ['inputType' => 'select', 'options' => $options];
            $interactionOptions['details'] = ['query' => $query];
            $callback = [
                'callback' => function($response) use (&$select, $options) {
                    if (empty($response)) { 
                        // throw new \Exception("Response was empty");
                        return false;
                    }
                    if (!isset($options[$response])) {
                        // \d($options);
                        // \d($response);
                        // throw new \Exception("Response was not valid: $response");
                        return false;
                    }
                    $select = $response;
                    return true;
                }
            ];
            Console::output("Waiting for interaction...");
            if (!$this->dataSource->action->createInteraction('Match Object', $interactionOptions, $callback)) {
                return false;
            }

            if ($select === 'new') {
                Console::output("Selected CREATE NEW");
                return null;
            } else {
                Console::output("Selected " . $resultsNice[$select]->descriptor);
                return $resultsNice[$select]->object;
            }
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
     * Set local fields
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setLocalFields($value)
    {
        $this->_localFields = $value;
    }

    /**
     * Get local fields
     * @return __return_getLocalFields_type__ __return_getLocalFields_description__
     */
    public function getLocalFields()
    {
        return $this->_localFields;
    }

    /**
     * Set foreign fields
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setForeignFields($value)
    {
        $this->_foreignFields = $value;
    }

    /**
     * Get foreign fields
     * @return __return_getForeignFields_type__ __return_getForeignFields_description__
     */
    public function getForeignFields()
    {
        return $this->_foreignFields;
    }

    /**
     * Get module
     * @return __return_getModule_type__ __return_getModule_description__
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }
}
