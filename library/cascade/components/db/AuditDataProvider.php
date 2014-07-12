<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db;
use infinite\helpers\ArrayHelper;

/**
 * ActiveRecord is the model class for table "{{%active_record}}".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AuditDataProvider extends \infinite\data\ActiveDataProvider
{
	public function handleInstructions($params)
	{
		$direction = ArrayHelper::getValue($params, 'direction', '_older');
		if ($direction === '_newer') {
			$lastAge = ArrayHelper::getValue($params, 'loadTimestamp', strtotime("1 year ago"));
		} else {
			
		}
	}
}
