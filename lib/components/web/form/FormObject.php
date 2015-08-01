<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\form;

/**
 * FormObject [[@doctodo class_description:cascade\components\web\form\FormObject]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class FormObject extends \canis\base\Object implements \canis\web\RenderInterface
{
    use FormObjectTrait;
}
