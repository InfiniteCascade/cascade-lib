<?php

namespace cascade\modules\TypeUser;

use Yii;

class Module extends \cascade\components\types\Module
{
    protected $_title = 'User';
    public $icon = 'fa fa-user';

    public $hasDashboard = true;

    public $widgetNamespace = 'cascade\\modules\\TypeUser\\widgets';
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
        return 'cascade\\models\\User';
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
        return [];
    }

    /**
     * @inheritdoc
     */
    public function taxonomies()
    {
        return [];
    }
}
