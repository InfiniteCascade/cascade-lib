<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors\auditable;

use Yii;
use infinite\helpers\ArrayHelper;
use cascade\models\ObjectFamiliarity;

/**
 * ActiveRecord is the model class for table "{{%active_record}}".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class AuditDataProvider extends \infinite\data\ActiveDataProvider
{
	public $scope = 'all';

	protected function clearParams(&$params)
	{
		foreach ($params as $key => &$value) {
			if (is_array($value)) {
				$this->clearParams($value);
			} elseif ($value === '') {
				unset($params[$key]);
			}
		}
	}

	public function getPackage()
	{
		return new AuditPackage($this);
	}

	public function handleInstructions($params)
	{
		$this->clearParams($params);
		//\d($params);
		$this->scope = ArrayHelper::getValue($params, 'scope', 'watching');
		$direction = ArrayHelper::getValue($params, 'direction', '_older');
		$limit = ArrayHelper::getValue($params, 'limit', 50);
		if ($direction === '_newer') {
			$lastTime = ArrayHelper::getValue($params, 'loadTimestamp', strtotime("1 year ago"));
			$this->query->andWhere($this->query->primaryAlias . '.created >= \'' . date("Y-m-d G:i:s", $lastTime) .'\'');
			$this->query->orderBy([$this->query->primaryAlias . '.created' => SORT_DESC]);
			$this->pagination->pageSize = false;
		} else { // _older
			$this->pagination->pageSize = $limit;
			$lastTime = ArrayHelper::getValue($params, 'lastItemTimestamp', time());
			$lastItem = ArrayHelper::getValue($params, 'lastItem', false);
			$this->query->andWhere($this->query->primaryAlias . '.created <= \'' . date("Y-m-d G:i:s", $lastTime) .'\'');
			if (!empty($lastItem)) {
				$this->query->andWhere(['not', [$this->query->primaryAlias . '.' . $this->query->primaryTablePk => $lastItem]]);
			}
			$this->query->orderBy([$this->query->primaryAlias . '.created' => SORT_ASC]);
		}

		if ($this->scope !== 'all' && !empty(Yii::$app->user->id)) {
			$subquery = ObjectFamiliarity::find();
			$subquery->andWhere([$subquery->primaryAlias .'.user_id' => Yii::$app->user->id]);
			if ($this->scope === 'watching') {
				$subquery->andWhere([$subquery->primaryAlias .'.watching' => 1]);
			}
			$subquery->select(['object_id']);
			$this->query->join('INNER JOIN', ['sof' => $subquery], ['or', '{{sof}}.[[object_id]] = {{'. $this->query->primaryAlias .'}}.[[direct_object_id]]', '{{sof}}.[[object_id]] = {{'. $this->query->primaryAlias .'}}.[[indirect_object_id]]']);
			$this->query->distinct = true;

		} else {
			$this->scope = 'all';
		}
	}
}
