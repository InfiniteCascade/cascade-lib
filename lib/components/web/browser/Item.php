<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\web\browser;

/**
 * Item [[@doctodo class_description:cascade\components\web\browser\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \canis\web\browser\Item
{
    /**
     * @var [[@doctodo var_type:objectType]] [[@doctodo var_description:objectType]]
     */
    public $objectType = false;

    /**
     * @inheritdoc
     */
    public function package()
    {
        return parent::package() + [
            'objectType' => $this->objectType,
        ];
    }
}
