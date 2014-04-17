<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\base;

/**
 * ClassManager [@doctodo write class description for ClassManager]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
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
            'Audit' => 'cascade\\models\\Audit',
            'Taxonomy' => 'cascade\\models\\Taxonomy',
            'TaxonomyType' => 'cascade\\models\\TaxonomyType',
            'RelationTaxonomy' => 'cascade\\models\\RelationTaxonomy',
            'ObjectTaxonomy' => 'cascade\\models\\ObjectTaxonomy',
            'SearchTermResult' => 'cascade\\components\\db\\behaviors\\SearchTermResult',
        ];
    }
}
