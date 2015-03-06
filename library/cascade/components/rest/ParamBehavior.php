<?php
namespace cascade\components\rest;

use yii\base\InvalidParamException;

/**
 * ParamBehavior [[@doctodo class_description:cascade\components\rest\ParamBehavior]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ParamBehavior extends \yii\base\Behavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Action::EVENT_BEFORE_RUN => [$this, 'beforeRun'],
        ];
    }

    /**
     * [[@doctodo method_description:params]].
     *
     * @return [[@doctodo return_type:params]] [[@doctodo return_description:params]]
     */
    public function params()
    {
        return [];
    }

    /**
     * Get required params.
     *
     * @return [[@doctodo return_type:getRequiredParams]] [[@doctodo return_description:getRequiredParams]]
     */
    public function getRequiredParams()
    {
        $params = [];
        foreach ($this->params() as $k => $v) {
            if (!is_numeric($k)) {
                if (!is_array($v)) {
                    $v = [];
                }
                $v['field'] = $k;
            }
            if (!isset($v['field'])) {
                continue;
            }
            if (!empty($v['required'])) {
                $params[] = $v['field'];
            }
        }

        return $params;
    }

    /**
     * Get param.
     *
     * @param [[@doctodo param_type:param]] $param [[@doctodo param_description:param]]
     *
     * @return [[@doctodo return_type:getParam]] [[@doctodo return_description:getParam]]
     */
    protected function getParam($param)
    {
        if (isset($_POST[$param])) {
            return $_POST[$param];
        } elseif (isset($_GET[$param])) {
            return $_GET[$param];
        }

        return;
    }
    /**
     * [[@doctodo method_description:beforeRun]].
     *
     * @param [[@doctodo param_type:event]] $event [[@doctodo param_description:event]]
     *
     * @throws InvalidParamException [[@doctodo exception_description:InvalidParamException]]
     * @return [[@doctodo return_type:beforeRun]] [[@doctodo return_description:beforeRun]]
     *
     */
    public function beforeRun($event)
    {
        foreach ($this->owner->params() as $param) {
            $value = $this->getParam($param);
            if (isset($value)) {
                $this->owner->{$param} = $value;
            }
        }
        foreach ($this->owner->requiredParams as $param) {
            if (!isset($this->owner->{$param})) {
                throw new InvalidParamException("The parameter '{$param}' is required for this object type.");
                $event->isValid = false;

                return false;
            }
        }
    }
}
