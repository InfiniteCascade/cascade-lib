<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface;

/**
 * Status [[@doctodo class_description:cascade\components\dataInterface\Status]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Status extends \canis\action\Status
{
  /**
   * @inheritdoc
   */
  public $linearTasks = false;
}
