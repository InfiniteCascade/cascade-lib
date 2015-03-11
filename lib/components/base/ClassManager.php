<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\base;

/**
 * ClassManager Class name helper for the application.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ClassManager extends \teal\base\ClassManager
{
    /**
     * @inheritdoc
     */
    public function baseClasses()
    {
        return [
            'Aca' => 'cascade\models\Aca',
            'Acl' => 'cascade\models\Acl',
            'AclRole' => 'cascade\models\AclRole',
            'Role' => 'cascade\models\Role',
            'Group' => 'cascade\models\Group',
            'Registry' => 'cascade\models\Registry',
            'Relation' => 'cascade\models\Relation',
            'RelationDependency' => 'cascade\models\RelationDependency',
            'User' => 'cascade\models\User',
            'IdentityProvider' => 'cascade\models\IdentityProvider',
            'Identity' => 'cascade\models\Identity',
            'Storage' => 'cascade\models\Storage',
            'StorageEngine' => 'cascade\models\StorageEngine',
            'ObjectTypeRegistry' => 'cascade\models\ObjectType',
            'ObjectFamiliarity' => 'cascade\models\ObjectFamiliarity',
            'Audit' => 'cascade\models\Audit',
            'Taxonomy' => 'cascade\models\Taxonomy',
            'TaxonomyType' => 'cascade\models\TaxonomyType',
            'RelationTaxonomy' => 'cascade\models\RelationTaxonomy',
            'ObjectTaxonomy' => 'cascade\models\ObjectTaxonomy',
            'SearchTermResult' => 'cascade\components\db\behaviors\SearchTermResult',
        ];
    }
}
