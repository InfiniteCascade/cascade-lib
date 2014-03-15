<?php
namespace cascade\components\base;

use Yii;

class ClassManager extends \infinite\base\ClassManager
{
	public function baseClasses()
	{
		return [
			'Aca' => 'cascade\\models\\Aca',
			'Acl' => 'cascade\\models\\Acl',
			'AclRole' => 'cascade\\models\\AclRole',
			'Role' => 'cascade\\models\\Role',
			'Group' => 'cascade\\models\\Group',
			'Registry' => 'cascade\\models\\Registry',
			'Relation' => 'cascade\\models\\Relation',
			'User' => 'cascade\\models\\User',
			'Storage' => 'cascade\\models\\Storage',
			'StorageEngine' => 'cascade\\models\\StorageEngine',
			'ObjectTypeRegistry' => 'cascade\\models\\ObjectType',
			'ObjectFamiliarity' => 'cascade\\models\\ObjectFamiliarity',
			'Taxonomy' => 'cascade\\models\\Taxonomy',
			'TaxonomyType' => 'cascade\\models\\TaxonomyType',
			'RelationTaxonomy' => 'cascade\\models\\RelationTaxonomy',
			'ObjectTaxonomy' => 'cascade\\models\\ObjectTaxonomy',
		];
	}
}

?>