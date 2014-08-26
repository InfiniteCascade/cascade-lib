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
	public $direction = '_older';
	public $context = false;

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
		return new AuditPackage($this, $this->context);
	}

	public function handleInstructions($params)
	{
		$this->clearParams($params);
		//\d($params);
		$this->scope = ArrayHelper::getValue($params, 'scope', 'watching');
		$direction = $this->direction = ArrayHelper::getValue($params, 'direction', '_older');
		$limit = ArrayHelper::getValue($params, 'limit', 25);
		$object = $this->context = ArrayHelper::getValue($params, 'object', false);
		if ($direction === '_newer') {
			$mostRecentItem = ArrayHelper::getValue($params, 'mostRecentItem', false);
			$this->query->params[':mostRecentItem'] = (int) $mostRecentItem;
			$this->query->andWhere($this->query->primaryAlias . '.id > :mostRecentItem');
			$this->query->orderBy([$this->query->primaryAlias . '.id' => SORT_DESC]);
			$this->pagination->pageSize = false;
			//\d(["newer", $this->query->createCommand()->rawSql]);exit;
		} else { // _older
			$this->pagination->pageSize = $limit;
			$lastTime = ArrayHelper::getValue($params, 'lastItemTimestamp', false);
			$lastItem = ArrayHelper::getValue($params, 'lastItem', false);
			if ($lastItem) {
				$this->query->params[':lastItem'] = (int) $lastItem;
				$this->query->andWhere($this->query->primaryAlias . '.id < :lastItem');
			}
			$this->query->orderBy([$this->query->primaryAlias . '.id' => SORT_DESC]); //SORT_ASC
			//\d($lastTime);
			//echo $this->query->createCommand()->rawSql;exit;
		}

		if ($this->scope === 'object' && $object) {
			$this->query->andWhere(['or', 
				[$this->query->primaryAlias . '.direct_object_id' => $object], 
				[$this->query->primaryAlias . '.indirect_object_id' => $object]]);
		} elseif ($this->scope !== 'all' && !empty(Yii::$app->user->id)) {
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
