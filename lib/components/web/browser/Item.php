<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
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
