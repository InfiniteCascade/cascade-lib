<?php
namespace cascade\components\rest;

use yii\base\InvalidParamException;

class ParamBehavior extends \yii\base\Behavior
{
    public function events()
    {
        return [
            Action::EVENT_BEFORE_RUN => [$this, 'beforeRun'],
        ];
    }

    public function params()
    {
        return [];
    }

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

    protected function getParam($param)
    {
        if (isset($_POST[$param])) {
            return $_POST[$param];
        } elseif (isset($_GET[$param])) {
            return $_GET[$param];
        }

        return;
    }
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
