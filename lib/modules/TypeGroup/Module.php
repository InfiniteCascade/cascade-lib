<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\modules\TypeGroup;

use cascade\models\Group;
use Yii;

/**
 * Module [[@doctodo class_description:cascade\modules\TypeGroup\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\types\Module
{
    /**
     * @inheritdoc
     */
    protected $_title = 'Group';
    /**
     * @inheritdoc
     */
    public $icon = 'fa fa-users';
    /**
     * @inheritdoc
     */
    public $hasDashboard = true;

    /**
     * @inheritdoc
     */
    public $widgetNamespace = 'cascade\modules\Group\widgets';
    /**
     * @inheritdoc
     */
    public $modelNamespace = false;

    /**
     * @inheritdoc
     */
    public function setup()
    {
        $results = [true];
        $results[] = Yii::$app->gk->allow('read', $this->objectTypeModel);

        return min($results);
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryModel()
    {
        return Group::className();
    }

    /**
     * @inheritdoc
     */
    public function widgets()
    {
        return parent::widgets();
    }

    /**
     * @inheritdoc
     */
    public function parents()
    {
        return [
            'Group' => ['handlePrimary' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function children()
    {
        return [
            'User' => ['handlePrimary' => false],
];
    }

    /**
     * @inheritdoc
     */
    public function taxonomies()
    {
        return [];
    }
}
