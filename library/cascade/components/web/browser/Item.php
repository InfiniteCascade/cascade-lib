<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\browser;

/**
 * Item [[@doctodo class_description:cascade\components\web\browser\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \infinite\web\browser\Item
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
