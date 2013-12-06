<?php
namespace cascade\components\db\behaviors;

class Taxonomy extends \infinite\db\behaviors\ActiveRecord
{
	public $taxonomyClass = 'cascade\\models\\Taxonomy';
	public $taxonomyTypeClass = 'cascade\\models\\TaxonomyType';
	public $viaModelClass = 'cascade\\models\\ObjectTaxonomy';
	public $relationKey = 'object_id';

	public $taxonomy_id;
}
?>