<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\TypeUser;

use Yii;

/**
 * Module [@doctodo write class description for Module]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\types\Module
{
    /**
     * @inheritdoc
     */
    protected $_title = 'User';
    /**
     * @inheritdoc
     */
    public $icon = 'fa fa-user';

    /**
     * @inheritdoc
     */
    public $hasDashboard = true;

    /**
     * @inheritdoc
     */
    public $widgetNamespace = 'cascade\\modules\\TypeUser\\widgets';
    /**
     * @inheritdoc
     */
    public $modelNamespace = false;

    public $searchWeight = 0;

    public $enableApiAccess = false;
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
