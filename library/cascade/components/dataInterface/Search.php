<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use cascade\components\helpers\StringHelper;
use infinite\helpers\ArrayHelper;
use infinite\helpers\Console;

/**
 * Search [@doctodo write class description for Search].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Search extends \infinite\base\Component
{
    /**
     */
    public static $interactive = true;
    /**
     */
    public $threshold = 50;
    /**
     */
    public $autoadjust = 1.5;
    /**
     */
    public $dataSource;

    public $foreignFilter;

    /**
     */
    protected $_localFields;
    /**
     */
    protected $_foreignFields = [];

    /**
     *
     */
    public function searchLocal(DataItem $item, $searchParams = [])
    {
        if (empty($searchParams['searchFields'])) {
            $searchParams['searchFields'] = $this->localFields;
        }
        if (empty($searchParams['searchFields'])) {
            return;
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
            return;
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
            return;
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
                $options['_o' . $optionNumber] = $result->descriptor . ' (' . $result->score . '%)';
                $optionNumber++;
            }
            $options['new'] = 'Create New Object';
            $select = false;
            $interactionOptions = ['inputType' => 'select', 'options' => $options];
            $interactionOptions['details'] = $item->foreignObject->attributes;
            $callback = [
                'callback' => function ($response) use (&$select, $options) {
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
                },
            ];
            Console::output("Waiting for interaction...");
            if (!$this->dataSource->action->createInteraction('Match Object (' . implode(' ', $query) . ')', $interactionOptions, $callback)) {
                return false;
            }

            if ($select === 'new') {
                Console::output("Selected CREATE NEW");

                return;
            } else {
                Console::output("Selected " . $resultsNice[$select]->descriptor);

                return $resultsNice[$select]->object;
            }
        }
    }

    /**
     *
     */
    public function searchForeign(DataItem $item)
    {
        return false;
    }

    /**
     * Set local fields.
     */
    public function setLocalFields($value)
    {
        $this->_localFields = $value;
    }

    /**
     * Get local fields.
     */
    public function getLocalFields()
    {
        return $this->_localFields;
    }

    /**
     * Set foreign fields.
     */
    public function setForeignFields($value)
    {
        $this->_foreignFields = $value;
    }

    /**
     * Get foreign fields.
     */
    public function getForeignFields()
    {
        return $this->_foreignFields;
    }

    /**
     * Get module.
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }
}
