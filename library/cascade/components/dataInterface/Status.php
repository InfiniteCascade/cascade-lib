<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

/**
 * Status [@doctodo write class description for Status]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Status extends \infinite\base\status\Status
{
    /**
     * @var __var__action_type__ __var__action_description__
     */
    protected $_action;
    /**
     * @var __var__errors_type__ __var__errors_description__
     */
    protected $_errors = [];

    /**
    * @inheritdoc
     */
    public function __construct($action)
    {
        $this->_action = $action;
    }

    /**
     * Prepares object for serialization.
     * @return __return___sleep_type__ __return___sleep_description__
     */
    public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["\0*\0_action", "\0*\0_registry"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }

        return $keys;
    }

    /**
     * __method_addError_description__
     * @param __param_message_type__ $message __param_message_description__
     */
    public function addError($message)
    {
        $this->_errors[] = $message;
    }

    /**
     * Get error
     * @return __return_getError_type__ __return_getError_description__
     */
    public function getError()
    {
        return !empty($this->_errors);
    }

}
