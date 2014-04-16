<?php

namespace cascade\modules\TypeGroup;

use Yii;

class Module extends \cascade\components\types\Module
{
    protected $_title = 'Group';
    public $icon = 'fa fa-users';
    public $hasDashboard = true;

    public $widgetNamespace = 'cascade\\modules\\Group\\widgets';
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
        return 'cascade\\models\\Group';
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
