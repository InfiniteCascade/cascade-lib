<?php

namespace cascade\models;

use Yii;

use infinite\base\exceptions\Exception;

use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class DeleteForm extends Model
{
	public $confirm = false;
	protected $_target;
	public $relationModel;
	public $relationshipWith;
	public $object;
	public $forceRelationshipDelete = false;
	public $forceObjectDelete = false;

	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return [
			[['confirm', 'target'], 'safe'],
		];
	}

	public function getTarget() {
		if (is_null($this->_target)) {
			if (!empty($this->relationModel)) {
				$this->target = 'relationship';
			} else {
				$this->target = 'object';
			}
		}
		return $this->_target;
	}

	public function setTarget($value) {
		if ($this->forceRelationshipDelete AND $this->forceObjectDelete) {
			$this->forceObjectDelete = false;
		}
		if (!empty($this->relationModel) AND $this->forceRelationshipDelete) {
			$this->_target = 'relationship';
		} elseif (!empty($this->object) AND $this->forceObjectDelete) {
			$this->_target = 'object';
		} elseif(in_array($value, array('relationship', 'object'))) {
			$this->_target = $value;
		} else {
			throw new Exception('Unknown deletion target '. $value);
		}
	}

	public function getTargetDescriptor() {
		if ($this->target === 'object') {
			return $this->object->descriptor;
		} else {
			return 'relationship';
		}
	}

	public function delete() {
		if ($this->target === 'object') {
			$result = true;
			if (!is_null($this->relationModel)) {
				$result = $this->relationModel->delete();
			}
			return $result && $this->object->delete();
		} else {
			return $this->relationModel->delete();
		}
	}
}
