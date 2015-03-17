<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\modules\WidgetWatching\widgets;

use canis\helpers\Html;
use yii\helpers\Url;

/**
 * Content [[@doctodo class_description:cascade\modules\WidgetWatching\widgets\Content]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Content extends \cascade\components\web\widgets\Widget
{
    /**
     * @var [[@doctodo var_type:_lazyObjectWidget]] [[@doctodo var_description:_lazyObjectWidget]]
     */
    protected $_lazyObjectWidget;
    /**
     * @var [[@doctodo var_type:id]] [[@doctodo var_description:id]]
     */
    public $id = 'Watching';

    /**
     * @inheritdoc
     */
    protected $_title = 'Activity';
    /**
     * @inheritdoc
     */
    public $icon = 'fa-eye';

    /**
     * @inheritdoc
     */
    public function init()
    {
        Html::addCssClass($this->htmlOptions, 'ic-watching-widget');

        parent::init();
    }

    /**
     * [[@doctodo method_description:generateStart]].
     *
     * @return [[@doctodo return_type:generateStart]] [[@doctodo return_description:generateStart]]
     */
    public function generateStart()
    {
        $this->htmlOptions['data-instructions'] = json_encode($this->refreshInstructions);
        if ($this->lazy) {
            Html::addCssClass($this->htmlOptions, 'widget-lazy');
        }

        return parent::generateStart();
    }

    /**
     * @inheritdoc
     */
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
     * Get grid cell settings.
     *
     * @return [[@doctodo return_type:getGridCellSettings]] [[@doctodo return_description:getGridCellSettings]]
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
        $parts[] = Html::tag('div', '', ['data-activity-feed' => json_encode($instructions), 'data-height' => json_encode(['min' => 1000, 'max' => 'body'])]);
        $parts[] = Html::tag('div', str_repeat(Html::tag('div', '', ['class' => 'widget-lazy-placeholder']), 30), ['class' => 'activity-feed-thinking']);

        return implode($parts);
    }

    /**
     * Get current scope.
     *
     * @return [[@doctodo return_type:getCurrentScope]] [[@doctodo return_description:getCurrentScope]]
     */
    public function getCurrentScope()
    {
        return $this->getState('scope', 'familiar');
    }

    /**
     * Get widget classes.
     *
     * @return [[@doctodo return_type:getWidgetClasses]] [[@doctodo return_description:getWidgetClasses]]
     */
    public function getWidgetClasses()
    {
        $classes = parent::getWidgetClasses();
        $classes[] = 'refreshable';

        return $classes;
    }

    /**
     * @inheritdoc
     */
    public function getHeaderMenu()
    {
        $items = [];
        $topChooser = [
            'label' => '<i class="fa fa-ellipsis-v"></i>',
            'linkOptions' => ['title' => 'Choose'],
            'url' => '#',
            'items' => [],
            'options' => ['class' => 'dropleft'],
        ];

        $isActive = $this->currentScope === 'all';
        $scopeKeyName = $this->stateKeyName('scope');
        $topChooser['items'][] = [
            'label' => 'All Items',
            'linkOptions' => [
                'title' => 'All Recent Activity',
                'data-state-change' => json_encode([$scopeKeyName => 'all']),
            ],
            'options' => [
                'class' => $isActive,
            ],
            'url' => '#',
            'active' => $isActive,
        ];

        $isActive = $this->currentScope === 'watching';
        $topChooser['items'][] = [
            'label' => 'Watched Items',
            'linkOptions' => [
                'title' => 'Watched Items',
                'data-state-change' => json_encode([$scopeKeyName => 'watching']),
            ],
            'options' => [
                'class' => ($isActive) ? 'active' : '',
            ],
            'url' => '#',
            'active' => $isActive,
        ];

        $isActive = $this->currentScope === 'familiar';
        $topChooser['items'][] = [
            'label' => 'Familiar Items',
            'linkOptions' => [
                'title' => 'Familiar Objects',
                'data-state-change' => json_encode([$scopeKeyName => 'familiar']),
            ],
            'options' => [
                'class' => ($isActive) ? 'active' : '',
            ],
            'url' => '#',
            'active' => $isActive,
        ];

        $items[] = $topChooser;

        return $items;
    }
}
