<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use yii\helpers\Url;

/**
 * SearchTermResult [@doctodo write class description for SearchTermResult]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class SearchTermResult extends \infinite\db\behaviors\SearchTermResult
{
    /**
     * @var __var__icon_type__ __var__icon_description__
     */
    protected $_icon;
    protected $_objectType;
    /**
     * @var __var__objectTypeDescriptor_type__ __var__objectTypeDescriptor_description__
     */
    /**
     * @var __var__objectType_type__ __var__objectType_description__
     */
    /**
     * @var __var__objectType_type__ __var__objectType_description__
     */
    /**
     * @var __var__objectType_type__ __var__objectType_description__
     */
    /**
     * @var __var__objectType_type__ __var__objectType_description__
     */
    /**
     * @var __var__objectType_type__ __var__objectType_description__
     */
    protected $_objectTypeDescriptor;
    /**
     * @var __var__url_type__ __var__url_description__
     */
    protected $_url;

    /**
     * __method_setUrl_description__
     * @param __param_value_type__ $value __param_value_description__
     */
    public function setUrl($value)
    {
        $this->_url = $value;
    }

    /**
     * __method_getUrl_description__
     * @return __return_getUrl_type__ __return_getUrl_description__
     */
    public function getUrl()
    {
        if (is_null($this->_url) && isset($this->object)) {
            $this->_url = Url::to($this->object->getUrl('view', [], false));
        }

        return $this->_url;
    }

    /**
     * __method_getIcon_description__
     * @return __return_getIcon_type__ __return_getIcon_description__
     */
    public function getIcon()
    {
        if (is_null($this->_icon) && isset($this->object)) {
            $this->_icon = ['class' => $this->object->objectType->icon, 'title' => $this->objectTypeDescriptor];
        }

        return $this->_icon;
    }

    /**
     * __method_setIcon_description__
     * @param __param_icon_type__ $icon __param_icon_description__
     */
    public function setIcon($icon)
    {
        if (is_string($icon)) {
            $icon = ['class' => $icon];
        }
        $this->_icon = $icon;
    }

    /**
     * __method_getObjectTypeDescriptor_description__
     * @return __return_getObjectTypeDescriptor_type__ __return_getObjectTypeDescriptor_description__
     */
    public function getObjectTypeDescriptor()
    {
        if (is_null($this->_objectTypeDescriptor) && isset($this->object)) {
            $this->_objectTypeDescriptor = $this->object->objectType->title->upperSingular;
        }

        return $this->_objectTypeDescriptor;
    }

    /**
     * __method_setObjectTypeDescriptor_description__
     * @param __param_type_type__ $type __param_type_description__
     */
    public function setObjectTypeDescriptor($type)
    {
        $this->_objectTypeDescriptor = $type;
    }

    /**
     * __method_getObjectType_description__
     * @return __return_getObjectType_type__ __return_getObjectType_description__
     */
    public function getObjectType()
    {
        if (is_null($this->_objectType) && isset($this->object)) {
            $this->_objectType = $this->object->objectType->systemId;
        }

        return $this->_objectType;
    }

    /**
     * __method_setObjectType_description__
     * @param __param_type_type__ $type __param_type_description__
     */
    public function setObjectType($type)
    {
        $this->_objectType = $type;
    }

    /**
    * @inheritdoc
    **/
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'icon' => $this->icon,
            'url' => $this->url,
            'objectType' => $this->objectType
        ]);
    }

    /**
    * @inheritdoc
    **/
    public function getScore()
    {
        if (empty($this->object->objectType->searchWeight)) {
            return 0;
        }

        return parent::getScore() * $this->object->objectType->searchWeight;
    }
}
