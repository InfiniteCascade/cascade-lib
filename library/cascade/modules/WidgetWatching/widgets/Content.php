<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\modules\WidgetWatching\widgets;

use Yii;
use yii\helpers\Url;
use infinite\helpers\Html;

/**
 * Content [@doctodo write class description for Content]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Content extends \cascade\components\web\widgets\Widget
{
    protected $_lazyObjectWidget;
	public $id = 'Watching';

    /**
     * @inheritdoc
     */
    protected $_title = 'Activity';
    /**
     * @inheritdoc
     */
    public $icon = 'fa-eye';

    public function init()
    {
    	Html::addCssClass($this->htmlOptions, 'ic-watching-widget');

    	parent::init();
    }

    public function generateStart()
    {
        $this->htmlOptions['data-instructions'] = json_encode($this->refreshInstructions);
        if ($this->lazy) {
            Html::addCssClass($this->htmlOptions, 'widget-lazy');
        }
        return parent::generateStart();
    }

    public function getTitle()
    {
        switch ($this->currentScope) {
        	case 'watching':
        		return $this->_title . ' of Watched Items';
        	break;
        	case 'familiar':
        		return $this->_title . ' of Familiar Items';
        	break;
        }
        return $this->_title;
    }

    /**
     * Get grid cell settings
     * @return __return_getGridCellSettings_type__ __return_getGridCellSettings_description__
     */
    public function getGridCellSettings()
    {
        $gridSettings = parent::getGridCellSettings();
        $gridSettings['columns'] = 6;
        $gridSettings['maxColumns'] = 12;

        return $gridSettings;
    }

    /**
    * @inheritdoc
     */
    public function generateContent()
    {
    	$parts = [];
    	$instructions = [];
    	$instructions['ajax']  = ['url' => Url::to(['/app/activity'])];
    	$instructions['scope'] = $this->currentScope;
    	$parts[] = Html::tag('div', '', ['data-activity-feed' => json_encode($instructions)]);
    	$parts[] = Html::tag('div', str_repeat(Html::tag('div', '', ['class' => 'widget-lazy-placeholder']), 30), ['class' => 'activity-feed-thinking']);
    	return implode($parts);
    }

    public function getCurrentScope()
    {
    	return $this->getState('scope', 'familiar');
    }


    public function getWidgetClasses()
    {
        $classes = parent::getWidgetClasses();
        $classes[] = 'refreshable';

        return $classes;
    }


    public function getHeaderMenu()
    {
    	$items = [];
    	$topChooser = [
            'label' => '<i class="fa fa-ellipsis-v"></i>',
            'linkOptions' => ['title' => 'Choose'],
            'url' => '#',
            'items' => [],
            'options' => ['class' => 'dropleft']
    	];


    	$isActive = $this->currentScope === 'all';
    	$scopeKeyName = $this->stateKeyName('scope');
    	$topChooser['items'][] = [
    		'label' => 'All Items',
            'linkOptions' => [
                'title' => 'All Recent Activity',
                'data-state-change' => json_encode([$scopeKeyName => 'all'])
            ],
            'options' => [
                'class' => $isActive
            ],
            'url' => '#',
            'active' => $isActive
        ];

    	$isActive = $this->currentScope === 'watching';
    	$topChooser['items'][] = [
    		'label' => 'Watched Items',
            'linkOptions' => [
                'title' => 'Watched Items',
                'data-state-change' => json_encode([$scopeKeyName => 'watching'])
            ],
            'options' => [
                'class' => ($isActive) ? 'active' : ''
            ],
            'url' => '#',
            'active' => $isActive
        ];


    	$isActive = $this->currentScope === 'familiar';
    	$topChooser['items'][] = [
    		'label' => 'Familiar Items',
            'linkOptions' => [
                'title' => 'Familiar Objects',
                'data-state-change' => json_encode([$scopeKeyName => 'familiar'])
            ],
            'options' => [
                'class' => ($isActive) ? 'active' : ''
            ],
            'url' => '#',
            'active' => $isActive
        ];

    	$items[] = $topChooser;
    	return $items;
    }
}
