<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface;

use cascade\components\helpers\StringHelper;
use cascade\models\Registry;
use canis\helpers\ArrayHelper;
use canis\helpers\Console;

/**
 * Search [[@doctodo class_description:cascade\components\dataInterface\Search]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Search extends \canis\base\Component
{
    /**
     * @var [[@doctodo var_type:interactive]] [[@doctodo var_description:interactive]]
     */
    public static $interactive = true;
    /**
     * @var [[@doctodo var_type:threshold]] [[@doctodo var_description:threshold]]
     */
    public $threshold = 70;

    public $defaultOptionThreshold = 90;
    /**
     * @var [[@doctodo var_type:superSafeAdjust]] [[@doctodo var_description:superSafeAdjust]]
     */
    public $superSafeAdjust = 1.3;
    /**
     * @var [[@doctodo var_type:dataSource]] [[@doctodo var_description:dataSource]]
     */
    public $dataSource;

    /**
     * @var [[@doctodo var_type:foreignFilter]] [[@doctodo var_description:foreignFilter]]
     */
    public $foreignFilter;

    /**
     * @var [[@doctodo var_type:_localFields]] [[@doctodo var_description:_localFields]]
     */
    protected $_localFields;
    /**
     * @var [[@doctodo var_type:_foreignFields]] [[@doctodo var_description:_foreignFields]]
     */
    protected $_foreignFields = [];

    /**
     * [[@doctodo method_description:searchLocal]].
     *
     * @param cascade\components\dataInterface\DataItem $item         [[@doctodo param_description:item]]
     * @param array                                     $searchParams [[@doctodo param_description:searchParams]] [optional]
     *
     * @return [[@doctodo return_type:searchLocal]] [[@doctodo return_description:searchLocal]]
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
        $resultsLeft = $totalResults = count($searchResults);
        foreach ($searchResults as $k => $r) {
            // if ($r->descriptor === $query[0]) { continue; }
            $score = (
                ((($resultsLeft/$totalResults) * 100) * .2)
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
            $resultsLeft--;
        }
        ArrayHelper::multisort($searchResults, 'scoreSort', SORT_DESC);
        $searchResults = array_values($searchResults);
        if (empty($searchResults)) {
            return;
        } elseif (($safeResult = $this->getSafeResult($searchResults))) {
            $this->dataSource->task->addInfo('Auto-matched "'. implode(' ', $query) .'" to "'. $safeResult->descriptor .'" (Score: '.round($safeResult->score, 2).')', [
                    'query' => $query,
                    'foreignObject' => $item->foreignObject->attributes,
                    'localObject' => ['id' => $safeResult->id, 'descriptor' => $safeResult->descriptor]
            ]);
            return $safeResult->object;
        } else {
            $options = [];
            $resultsNice = [];
            $optionNumber = 1;

            $defaultOption = 'new';
            if ($searchResults[0]->score > $this->defaultOptionThreshold) {
                $defaultOption = '1';
            }
            foreach ($searchResults as $result) {
                $resultsNice['' . $optionNumber] = $result;
                $options['' . $optionNumber] = $result->descriptor . ' (' . round($result->score) . '%)';
                $optionNumber++;
            }
            $options['new'] = 'Create New Object';
            $options['selectObject'] = 'Select Object';
            $select = false;
            $interactionOptions = ['inputType' => 'select', 'options' => $options];
            $interactionOptions['details'] = $item->foreignObject->attributes;
            $interactionOptions['default'] = $defaultOption;
            $callback = [
                'callback' => function ($response) use (&$select, $options) {
                    if (empty($response)) {
                        return false;
                    }
                    if (substr($response, 0, 2) !== '*-' && !isset($options[$response])) {
                        return false;
                    }
                    $select = $response;

                    return true;
                },
            ];
            if (!$this->dataSource->action->createInteraction('Match Object (' . implode(' ', $query) . ')', $interactionOptions, $callback)) {
                return false;
            }

            if ($select === 'new') {
                $this->dataSource->task->addInfo('For "'. implode(' ', $query) .'" user chose to to create new object', [
                        'query' => $query,
                        'foreignObject' => $item->foreignObject->attributes
                ]);
                return;
            } elseif (substr($select, 0, 2) === '*-') {
                $matchedObjectId = substr($select, 2);
                $matchedObject = Registry::getObject($matchedObjectId, false);
                if (!$matchedObject) {
                    $this->dataSource->task->addWarning('For "'. implode(' ', $query) .'" user tried to match it to a different object, but object wasn\'t found! Created new object.', [
                            'query' => $query,
                            'foreignObject' => $item->foreignObject->attributes,
                            'matchedObject' => $matchedObjectId
                    ]);
                    return;
                }
                $this->dataSource->task->addInfo('For "'. implode(' ', $query) .'" matched to an existing object "'. $matchedObject->descriptor .'"', [
                        'query' => $query,
                        'foreignObject' => $item->foreignObject->attributes,
                        'matchedObject' => $matchedObject->attributes
                ]);
                return $matchedObject;
            } else {
                $this->dataSource->task->addInfo('User matched "'. implode(' ', $query) .'" to "'. $resultsNice[$select]->descriptor . '"', [
                        'query' => $query,
                        'foreignObject' => $item->foreignObject->attributes,
                        'localObject' => $resultsNice[$select]->id
                ]);
                return $resultsNice[$select]->object;
            }
        }
    }

    /**
     * [[@doctodo method_description:searchForeign]].
     *
     * @param cascade\components\dataInterface\DataItem $item [[@doctodo param_description:item]]
     *
     * @return [[@doctodo return_type:searchForeign]] [[@doctodo return_description:searchForeign]]
     */
    public function searchForeign(DataItem $item)
    {
        return false;
    }

    /**
     * Set local fields.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setLocalFields($value)
    {
        $this->_localFields = $value;
    }

    /**
     * Get local fields.
     *
     * @return [[@doctodo return_type:getLocalFields]] [[@doctodo return_description:getLocalFields]]
     */
    public function getLocalFields()
    {
        return $this->_localFields;
    }

    /**
     * Set foreign fields.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setForeignFields($value)
    {
        $this->_foreignFields = $value;
    }

    /**
     * Get foreign fields.
     *
     * @return [[@doctodo return_type:getForeignFields]] [[@doctodo return_description:getForeignFields]]
     */
    public function getForeignFields()
    {
        return $this->_foreignFields;
    }

    /**
     * Get module.
     *
     * @return [[@doctodo return_type:getModule]] [[@doctodo return_description:getModule]]
     */
    public function getModule()
    {
        return $this->dataSource->module;
    }

    protected function getSafeResult($results)
    {
        // does it have a result?
        if (!isset($results[0])) {
            return false;
        }
        $firstResult = $results[0];

        // does it have a second option?
        $secondResult = false;
        if (isset($results[1])) {
            $secondResult = $results[1];
        }

        // is it super safe?
        if ($firstResult->score < ($this->threshold * $this->superSafeAdjust)) {
            return false;
        }

        // is its score the same as the next best result?
        if ($secondResult && $secondResult->score === $firstResult->score) {
            return false;
        }

        return $firstResult;
    }
}
