<?php

namespace cascade\models;

use Yii;

use \infinite\data\ActiveDataProvider;
use \infinite\db\ActiveQuery;

/**
 * This is the model class for table "object_familiarity".
 *
 * @property string $object_id
 * @property string $user_id
 * @property string $model
 * @property boolean $watching
 * @property boolean $created
 * @property integer $modified
 * @property integer $accessed
 * @property integer $familiarity
 * @property string $session
 * @property string $last_modified
 * @property string $last_accessed
 * @property string $first_accessed
 *
 * @property User $user
 * @property Registry $object
 */
class ObjectFamiliarity extends \cascade\components\db\ActiveRecord
{
	const ACCESSED_FAMILIARITY = 1;
	const MODIFIED_FAMILIARITY = 2;
	const CREATED_FAMILIARITY = 3;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'object_familiarity';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['object_id', 'user_id', 'familiarity'], 'required'],
			[['watching', 'created'], 'boolean'],
			[['modified', 'accessed', 'familiarity'], 'integer'],
			[['last_modified', 'last_accessed', 'first_accessed'], 'safe'],
			[['object_id', 'user_id'], 'string', 'max' => 36],
			[['model'], 'string', 'max' => 255],
			[['session'], 'string', 'max' => 32]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'object_id' => 'Object ID',
			'user_id' => 'User ID',
			'model' => 'Model',
			'watching' => 'Watching',
			'created' => 'Created',
			'modified' => 'Modified',
			'accessed' => 'Accessed',
			'familiarity' => 'Familiarity',
			'session' => 'Session',
			'last_modified' => 'Last Modified',
			'last_accessed' => 'Last Accessed',
			'first_accessed' => 'First Accessed',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getUser()
	{
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getObject()
	{
		return $this->hasOne(Registry::className(), ['id' => 'object_id']);
	}


	/**
	 *
	 *
	 * @param unknown $object
	 * @param unknown $user   (optional)
	 * @return unknown
	 */
	public static function created($object, $user = null) {
		$objectId = $object->id;
		$objectModel = addslashes($object->modelAlias);
		if (is_null($user) and isset(Yii::$app->user)) {
			$user = Yii::$app->user->id;
		}
		if (is_object($user)) {
			$user = $user->primaryKey;
		}
		if (empty($user) or empty(Yii::$app->session)) {
			return false;
		}
		$session = Yii::$app->session->id;

		$tableName = self::tableName();
		$familiarityUp = self::CREATED_FAMILIARITY;
		$query = "INSERT INTO `{$tableName}` SET `created`=1, `familiarity`={$familiarityUp}, `last_modified`=NOW(), `last_accessed`=NOW(), `first_accessed`=NOW(), `object_id`='{$objectId}', `model`='{$objectModel}', `user_id`='{$user}' ON DUPLICATE KEY UPDATE `modified`=`modified`+1, `familiarity`=`familiarity`+{$familiarityUp}, `session`='{$session}',  last_modified=NOW();";
		$command = Yii::$app->db->createCommand($query);
		return $command->execute();
	}


	/**
	 *
	 *
	 * @param unknown $object
	 * @param unknown $user   (optional)
	 * @return unknown
	 */
	public static function modified($object, $user = null) {
		$objectId = $object->id;
		$objectModel = addslashes($object->modelAlias);
		if (is_null($user) and isset(Yii::$app->user)) {
			$user = Yii::$app->user->id;
		}
		if (is_object($user)) {
			$user = $user->primaryKey;
		}
		if (empty($user) or empty(Yii::$app->session)) {
			return false;
		}
		$session = Yii::$app->session->id;

		$tableName = self::tableName();
		$familiarityUp = self::MODIFIED_FAMILIARITY;
		$query = "INSERT INTO `{$tableName}` SET `modified`=1, `familiarity`={$familiarityUp}, `last_modified`=NOW(), `last_accessed`=NOW(), `first_accessed`=NOW(), `object_id`='{$objectId}', `model`='{$objectModel}', `user_id`='{$user}', `session`='{$session}' ON DUPLICATE KEY UPDATE `modified`=`modified`+1, `familiarity`=`familiarity`+{$familiarityUp}, `session`='{$session}',  last_modified=NOW(), last_accessed=NOW();";
		$command = Yii::$app->db->createCommand($query);
		return $command->execute();
	}


	/**
	 *
	 *
	 * @param unknown $object
	 * @param unknown $user   (optional)
	 * @return unknown
	 */
	public static function accessed($object, $user = null) {
		$objectId = $object->id;
		$objectModel = addslashes($object->modelAlias);
		if (is_null($user) and isset(Yii::$app->user)) {
			$user = Yii::$app->user->id;
		}
		if (is_object($user)) {
			$user = $user->primaryKey;
		}
		if (empty($user) or empty(Yii::$app->session)) {
			return false;
		}
		$session = Yii::$app->session->id;

		$tableName = self::tableName();
		$familiarityUp = self::ACCESSED_FAMILIARITY;
		$query = "INSERT INTO `{$tableName}` SET `accessed`=1, `familiarity`={$familiarityUp}, `last_accessed`=NOW(), `first_accessed`=NOW(), `object_id`='{$objectId}', `model`='{$objectModel}', `user_id`='{$user}', `session`='{$session}' ON DUPLICATE KEY UPDATE `accessed`=IF((`session` IS NULL OR `session` != '{$session}'), `accessed`+1, `accessed`), `familiarity`=IF((`session` IS NULL OR `session` != '{$session}'), `familiarity`+{$familiarityUp}, `familiarity`), `session`='{$session}', last_accessed=NOW();";
		$command = Yii::$app->db->createCommand($query);
		return $command->execute();
	}

	public static function familiarObjects($model, $limit = 10) {
		$queryModel = new $model;
		$query = $model::find();
		$query->with(['familiarity']);

		if (!is_null($limit)) {
			$query->limit = $limit;
		}
		$query->orderBy = ['familiarity.familiarity' => SORT_DESC, 'familiarity.last_accessed' => SORT_DESC];
		return $query->all();
	}

	public static function familiarObjectsProvider($model, $state) {
		$queryModel = new $model;
		$query = $model::find();
		$query->with(['familiarity']);

		return new ActiveDataProvider([
				'query' => $query,
				'pagination' => [
				],
				'state' => $state,
				'sort' => [
					'defaultOrder' => 'familiarity.familiarity DESC, familiarity.last_accessed DESC',
					'attributes' => [
						'familiarity' => [
							'asc' => 'familiarity.familiarity ASC, '. $queryModel->defaultOrder,
							'desc' => 'familiarity.familiarity DESC, '. $queryModel->defaultOrder,
						],
						'last_accessed' => [
							'asc' => 'familiarity.last_accessed ASC, '. $queryModel->defaultOrder,
							'desc' => 'familiarity.last_accessed DESC, '. $queryModel->defaultOrder,
						],
						'*',
					],
				],
			]);
	}

	public static function familiarObjectsList($model, $limit = 10) {
		$f = self::familiarObjects($model, $limit);
		return ArrayHelper::map($f, 'id', 'descriptor');
	}

}
