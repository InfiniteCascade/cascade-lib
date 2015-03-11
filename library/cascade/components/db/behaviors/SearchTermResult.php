<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\db\behaviors;

use yii\helpers\Url;

/**
 * SearchTermResult [[@doctodo class_description:cascade\components\db\behaviors\SearchTermResult]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SearchTermResult extends \teal\db\behaviors\SearchTermResult
{
    /**
     * @var [[@doctodo var_type:_icon]] [[@doctodo var_description:_icon]]
     */
    protected $_icon;
    /**
     * @var [[@doctodo var_type:_objectType]] [[@doctodo var_description:_objectType]]
     */
    protected $_objectType;
    /**
     * @var [[@doctodo var_type:_objectTypeDescriptor]] [[@doctodo var_description:_objectTypeDescriptor]]
     */
    protected $_objectTypeDescriptor;
    /**
     * @var [[@doctodo var_type:_url]] [[@doctodo var_description:_url]]
     */
    protected $_url;

    /**
     * Set url.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     */
    public function setUrl($value)
    {
        $this->_url = $value;
    }

    /**
     * Get url.
     *
     * @return [[@doctodo return_type:getUrl]] [[@doctodo return_description:getUrl]]
     */
    public function getUrl()
    {
        if (is_null($this->_url) && isset($this->object)) {
            $this->_url = Url::to($this->object->getUrl('view', [], false));
        }

        return $this->_url;
    }

    /**
     * Get icon.
     *
     * @return [[@doctodo return_type:getIcon]] [[@doctodo return_description:getIcon]]
     */
    public function getIcon()
    {
        if (is_null($this->_icon) && isset($this->object)) {
            $this->_icon = ['class' => $this->object->objectType->icon, 'title' => $this->objectTypeDescriptor];
        }

        return $this->_icon;
    }

    /**
     * Set icon.
     *
     * @param [[@doctodo param_type:icon]] $icon [[@doctodo param_description:icon]]
     */
    public function setIcon($icon)
    {
        if (is_string($icon)) {
            $icon = ['class' => $icon];
        }
        $this->_icon = $icon;
    }

    /**
     * Get object type descriptor.
     *
     * @return [[@doctodo return_type:getObjectTypeDescriptor]] [[@doctodo return_description:getObjectTypeDescriptor]]
     */
    public function getObjectTypeDescriptor()
    {
        if (is_null($this->_objectTypeDescriptor) && isset($this->object)) {
            $this->_objectTypeDescriptor = $this->object->objectType->title->upperSingular;
        }

        return $this->_objectTypeDescriptor;
    }

    /**
     * Set object type descriptor.
     *
     * @param [[@doctodo param_type:type]] $type [[@doctodo param_description:type]]
     */
    public function setObjectTypeDescriptor($type)
    {
        $this->_objectTypeDescriptor = $type;
    }

    /**
     * Get object type.
     *
     * @return [[@doctodo return_type:getObjectType]] [[@doctodo return_description:getObjectType]]
     */
    public function getObjectType()
    {
        if (is_null($this->_objectType) && isset($this->object)) {
            $this->_objectType = $this->object->objectType->systemId;
        }

        return $this->_objectType;
    }

    /**
     * Set object type.
     *
     * @param [[@doctodo param_type:type]] $type [[@doctodo param_description:type]]
     */
    public function setObjectType($type)
    {
        $this->_objectType = $type;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'icon' => $this->icon,
            'url' => $this->url,
            'objectType' => $this->objectType,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getScore()
    {
        if (empty($this->object->objectType->searchWeight)) {
            return 0;
        }

        return parent::getScore() * $this->object->objectType->searchWeight;
    }
}
