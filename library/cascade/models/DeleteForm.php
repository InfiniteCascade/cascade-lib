<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use cascade\components\types\Relationship;
use infinite\base\exceptions\Exception;
use yii\base\Model;

/**
 * DeleteForm [@doctodo write class description for DeleteForm].
 *
 * LoginForm is the model behind the login form.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeleteForm extends Model
{
    /**
     */
    public $confirm = false;
    /**
     */
    protected $_target;
    /**
     */
    public $relationModel;
    /**
     */
    public $relationshipWith;
    /**
     */
    public $relationship;
    /**
     */
    public $object;
    /**
     */
    protected $_possibleTargets;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['confirm', 'target'], 'safe'],
        ];
    }

    /**
     * Get labels.
     */
    public function getLabels()
    {
        $labels = [];
        $labels['delete_object'] = [
            'short' => 'Delete ' . $this->object->objectType->title->getSingular(true),
            'long' => 'delete the ' . $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em>',
            'past' => $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em> has been deleted',
            'options' => ['class' => 'btn-danger'],
            'response' => 'home',
        ];
        $labels['archive_object'] = [
            'short' => 'Archive ' . $this->object->objectType->title->getSingular(true),
            'long' => 'archive the ' . $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em>',
            'past' => $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em> has been archived',
            'response' => 'refresh',
        ];
        $labels['unarchive_object'] = [
            'short' => 'Unarchive ' . $this->object->objectType->title->getSingular(true),
            'long' => 'unarchive the ' . $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em>',
            'past' => $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em> has been unarchived',
            'response' => 'refresh',
        ];
        if (isset($this->relationshipWith)) {
            $labels['delete_relationship'] = [
                'short' => 'Delete Relationship',
                'long' => 'delete the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em>',
                'past' => 'the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em> has been deleted',
                'options' => ['class' => 'btn-warning'],
            ];
            $labels['end_relationship'] = [
                'short' => 'End Relationship',
                'long' => 'end the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em>',
                'past' => 'the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em> has been ended',
            ];
        }

        return $labels;
    }

    /**
     * Get target.
     */
    public function getTarget()
    {
        if (is_null($this->_target) && !empty($this->possibleTargets)) {
            $this->_target = $this->possibleTargets[0];
        }

        return $this->_target;
    }

    /**
     *
     */
    public function canDeleteObject()
    {
        if ($this->object->objectType->hasDashboard && isset($this->relationship) && !$this->relationship->isHasOne()) {
            return false;
        }

        return $this->object->can('delete');
    }

    /**
     *
     */
    public function canArchiveObject()
    {
        if ($this->object->objectType->hasDashboard && isset($this->relationship) && !$this->relationship->isHasOne()) {
            return false;
        }

        return $this->object->can('archive');
    }

    /**
     *
     */
    public function canDeleteRelation()
    {
        if (isset($this->relationModel)) {
            if (!$this->object->allowRogue($this->relationModel)) {
                return false;
            }
            if (!$this->object->canDeleteAssociation($this->relationshipWith)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     *
     */
    public function canEndRelation()
    {
        if (!isset($this->relationModel) || !isset($this->relationship)) {
            return false;
        }
        if (!$this->relationship->temporal) {
            return false;
        }
        if (!$this->object->canUpdateAssociation($this->relationshipWith)) {
            return false;
        }

        return true;
    }

    /**
     *
     */
    public function hasObjectTargets()
    {
        foreach ($this->possibleTargets as $target) {
            if (in_array($target, ['delete_object', 'archive_object'])) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     */
    public function hasRelationshipTargets()
    {
        foreach ($this->possibleTargets as $target) {
            if (in_array($target, ['end_relationship', 'delete_relationship'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get possible targets.
     */
    public function getPossibleTargets()
    {
        if (is_null($this->_possibleTargets)) {
            $this->_possibleTargets = [];

            if ($this->canEndRelation()) {
                $this->_possibleTargets[] = 'end_relationship';
            }

            if ($this->canDeleteRelation()) {
                $this->_possibleTargets[] = 'delete_relationship';
            }

            if ($this->canArchiveObject()) {
                if ($this->object->archived) {
                    $this->_possibleTargets[] = 'unarchive_object';
                } else {
                    $this->_possibleTargets[] = 'archive_object';
                }
            }

            if ($this->canDeleteObject()) {
                $this->_possibleTargets[] = 'delete_object';
            }
        }

        return $this->_possibleTargets;
    }

    /**
     * Set target.
     */
    public function setTarget($value)
    {
        if (in_array($value, $this->possibleTargets)) {
            $this->_target = $value;
        } else {
            throw new Exception('Unknown deletion target ' . $value);
        }
    }

    /**
     * Get target label.
     */
    public function getTargetLabel()
    {
        if (!isset($this->labels[$this->target])) {
            return ['long' => 'unknown', 'short' => 'unknown'];
        }

        return $this->labels[$this->target];
    }

    /**
     * Get target descriptor.
     */
    public function getTargetDescriptor()
    {
        if ($this->target === 'object') {
            return $this->object->descriptor;
        } else {
            return 'relationship';
        }
    }

    /**
     *
     */
    public function handle()
    {
        $result = false;
        switch ($this->target) {
            case 'delete_object':
                $result = true;
                if (!is_null($this->relationModel)) {
                    $result = $this->relationModel->suppressAudit()->delete();
                }
                $result = $result && $this->object->delete();
            break;
            case 'archive_object':
                $result = $this->object->archive();
            break;
            case 'unarchive_object':
                $result = $this->object->unarchive();
            break;
            case 'delete_relationship':
                $result = $this->relationModel->delete();
            break;
            case 'end_relationship':
                $result = $this->relationModel->endRelationship();
            break;
        }

        return $result;
    }
}
