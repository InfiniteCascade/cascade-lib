<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\fields;

/**
 * Model [[@doctodo class_description:cascade\components\db\fields\Model]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Model extends Base
{
    /**
     * @inheritdoc
     */
    public $formFieldClass = 'cascade\components\web\form\fields\Model';
    /**
     * @inheritdoc
     */
    public function determineLocations()
    {
        if (isset($this->model)) {
            if (empty($this->value)) {
                return [self::LOCATION_HIDDEN];
            }
            $descriptorField = $this->model->descriptorField;
            $subdescriptorFields = $this->model->subdescriptorFields;
            if (!is_array($descriptorField)) {
                $descriptorField = [$descriptorField];
            }
            if (in_array($this->field, $descriptorField)) {
                return [self::LOCATION_HEADER];
            } elseif (in_array($this->field, $subdescriptorFields)) {
                return [self::LOCATION_SUBHEADER];
            }
        }

        return parent::determineLocations();
    }
}
