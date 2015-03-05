<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\taxonomy;

use infinite\helpers\ArrayHelper;

/**
 * Item [[@doctodo class_description:cascade\components\taxonomy\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \infinite\base\collector\Item
{
    /**
     * @var [[@doctodo var_type:name]] [[@doctodo var_description:name]]
     */
    public $name;
    /**
     * @var [[@doctodo var_type:systemId]] [[@doctodo var_description:systemId]]
     */
    public $systemId;
    /**
     * @var [[@doctodo var_type:systemVersion]] [[@doctodo var_description:systemVersion]]
     */
    public $systemVersion = 1;
    /**
     * @var [[@doctodo var_type:initialTaxonomies]] [[@doctodo var_description:initialTaxonomies]]
     */
    public $initialTaxonomies = [];
    /**
     * @var [[@doctodo var_type:models]] [[@doctodo var_description:models]]
     */
    public $models = [];
    /**
     * @var [[@doctodo var_type:modules]] [[@doctodo var_description:modules]]
     */
    public $modules = [];
    /**
     * @var [[@doctodo var_type:multiple]] [[@doctodo var_description:multiple]]
     */
    public $multiple = false;
    /**
     * @var [[@doctodo var_type:required]] [[@doctodo var_description:required]]
     */
    public $required = false;
    /**
     * @var [[@doctodo var_type:default]] [[@doctodo var_description:default]]
     */
    public $default = [];
    /**
     * @var [[@doctodo var_type:parentUnique]] [[@doctodo var_description:parentUnique]]
     */
    public $parentUnique = false;

    /**
     * @var [[@doctodo var_type:_taxonomies]] [[@doctodo var_description:_taxonomies]]
     */
    protected $_taxonomies;

    /**
     * [[@doctodo method_description:package]].
     *
     * @param array $override [[@doctodo param_description:override]] [optional]
     *
     * @return [[@doctodo return_type:package]] [[@doctodo return_description:package]]
     */
    public function package($override = [])
    {
        return array_merge([
            'id' => $this->systemId,
            'name' => $this->name,
            'multiple' => $this->multiple,
            'required' => $this->required,
            'default' => $this->default,
            'taxonomies' => $this->taxonomyList,
        ], $override);
    }
    /**
     * Get taxonomies.
     *
     * @return unknown
     */
    public function getTaxonomies()
    {
        if (is_null($this->_taxonomies)) {
            $this->_taxonomies = $this->object->taxonomies;
        }

        return $this->_taxonomies;
    }
    /**
     * Get taxonomy list.
     *
     * @return unknown
     */
    public function getTaxonomyList()
    {
        return ArrayHelper::map($this->getTaxonomies(), 'id', 'name');
    }

    /**
     * Get taxonomy.
     *
     * @return unknown
     */
    public function getTaxonomy($system_id)
    {
        foreach ($this->getTaxonomies() as $taxonomy) {
            if ($taxonomy->system_id === $system_id) {
                return $taxonomy;
            }
        }

        return false;
    }

    /**
     * [[@doctodo method_description:addTaxonomy]].
     */
    public function addTaxonomy($taxonomy)
    {
        $this->taxonomies;
        if (is_null($this->_taxonomies)) {
            $this->taxonomies;
        }
        $this->_taxonomies[] = $taxonomy;
    }
}
