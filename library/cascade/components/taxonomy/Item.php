<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\taxonomy;

use infinite\helpers\ArrayHelper;

/**
 * Item [@doctodo write class description for Item]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class Item extends \infinite\base\collector\Item
{
    /**
     * @var __var_name_type__ __var_name_description__
     */
    public $name;
    /**
     * @var __var_systemId_type__ __var_systemId_description__
     */
    public $systemId;
    /**
     * @var __var_systemVersion_type__ __var_systemVersion_description__
     */
    public $systemVersion = 1;
    /**
     * @var __var_initialTaxonomies_type__ __var_initialTaxonomies_description__
     */
    public $initialTaxonomies = [];
    /**
     * @var __var_models_type__ __var_models_description__
     */
    public $models = [];
    /**
     * @var __var_modules_type__ __var_modules_description__
     */
    public $modules = [];
    /**
     * @var __var_multiple_type__ __var_multiple_description__
     */
    public $multiple = false;
    /**
     * @var __var_required_type__ __var_required_description__
     */
    public $required = false;
    /**
     * @var __var_default_type__ __var_default_description__
     */
    public $default = [];
    /**
     * @var __var_parentUnique_type__ __var_parentUnique_description__
     */
    public $parentUnique = false;

    /**
     * @var __var__taxonomies_type__ __var__taxonomies_description__
     */
    protected $_taxonomies;

    /**
     * __method_package_description__
     * @param array $override __param_override_description__ [optional]
     * @return __return_package_type__ __return_package_description__
     */
    public function package($override = [])
    {
        return array_merge([
            'id' => $this->systemId,
            'name' => $this->name,
            'multiple' => $this->multiple,
            'required' => $this->required,
            'default' => $this->default,
            'taxonomies' => $this->taxonomyList
        ], $override);
    }
    /**
     * __method_getTaxonomies_description__
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
     * __method_getTaxonomyList_description__
     * @return unknown
     */
    public function getTaxonomyList()
    {
        return ArrayHelper::map($this->getTaxonomies(), 'id', 'name');
    }

    /**
     * __method_getTaxonomy_description__
     * @param __param_system_id_type__ $system_id __param_system_id_description__
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
     * __method_addTaxonomy_description__
     * @param __param_taxonomy_type__ $taxonomy __param_taxonomy_description__
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
