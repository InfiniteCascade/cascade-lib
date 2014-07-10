<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;

use infinite\helpers\Html;
use infinite\web\grid\Row;
use infinite\web\grid\Cell;
use infinite\helpers\ArrayHelper;
use yii\bootstrap\ButtonDropdown;

trait ListWidgetTrait
{
    public $renderPager = true;
    public $emptyMessage = 'No items exist.';
    public $defaultContentRow = [
        'class' => 'list-group-item-text',
        'tag' => 'div'
    ];
    protected $_renderContentTemplate;
    protected $_context;

    public function contentTemplate($model)
    {
        if ($model->objectType->hasDashboard && $model->can('read')) {
            return [
                'viewLink' => ['class' => 'list-group-item-heading', 'tag' => 'h5'],
                'subdescriptor' => ['class' => 'list-group-item-text ic-object-subheader']
            ];
        } else {
            return [
                'descriptor' => ['class' => 'list-group-item-heading', 'tag' => 'h5'],
                'subdescriptor' => ['class' => 'list-group-item-text ic-object-subheader']
            ];
        }
    }

    public function buildContext($object = null)
    {
        $context = [];
        return $context;
    }

    public function getContext($object = null)
    {
        if (!isset($this->_context)) {
            $this->_context = $this->buildContext($object);
        }
        return $this->_context;
    }

    public function getItemFieldValue($model, $fieldName, $settings = [])
    {
        if (is_array($fieldName)) {
            foreach ($fieldName as $fName => $fSettings) {
                if (is_numeric($fName)) {
                    $fName = $fSettings;
                    $fSettings = [];
                }
                $aSettings = array_merge($settings, $fSettings);
                $value = $this->getItemFieldValue($model, $fName, $aSettings);
                if (!empty($value)) {
                    return $value;
                }
            }
            return null;
        }
        $settings = array_merge($this->defaultContentRow, $settings);
        $context = $this->getContext($model);

        $fieldOptions = isset($settings['fieldOptions']) ? $settings['fieldOptions'] : [];
        switch ($fieldName) {
            case 'subdescriptor':
                $functionName = 'get'.ucfirst($fieldName);
                $value = $model->{$functionName}($context);
            break;
            default:
                $value = $model->getFieldValue($fieldName, $fieldOptions, $context);
            break;
        }
        $depth = 3;
        while ($depth > 0 && is_array($value)) {
            $depth--;
            if (isset($value['rich'])) {
                $value = $value['rich'];
            } else {
                $value = array_shift($value);
            }
        }
        if (!empty($value)) {
            return $value;
        }
        return null;
    }

    public function renderItemContent($model, $key, $index)
    {
        $template = $this->contentTemplate($model);
        $parts = [];
        if (isset($model->relationModel) && isset($model->relationModel['id'])) {
            // $parts[] = $model->relationModel['id'];
            $template = array_merge([
                    'relationTaxonomies' => ['class' => 'list-group-item-pre', 'tag' => 'div']
                ], $template);
        }
        foreach ($template as $fieldName => $settings) {
            if (is_numeric($fieldName)) {
                $fieldName = $settings;
                $settings = [];
            }
            if (is_array($fieldName)) {
                $subparts = [];
                $subrowSettings = [];
                if (isset($fieldName['settings'])) {
                    $subrowSettings = $fieldName['settings'];
                }
                foreach ($fieldName as $subFieldName => $subSettings) {
                    if ($fieldName === 'settings') { continue; }
                    if (is_numeric($subFieldName)) {
                        $subFieldName = $subSettings;
                        $subSettings = [];
                    }
                    $this->renderItemContentRow($subparts, $subFieldName, $subSettings, $model, $key, $index);
                }
                if (!empty($subparts)) {
                    $row = new Row;
                    $columnSize = floor(12 / count($subparts));
                    foreach ($subparts as $subpart) {
                        $cell = new Cell(['content' => $subpart, 'phoneColumns' => $columnSize, 'phoneSize' => 'auto', 'tabletSize' => false, 'mediumDesktopSize' => false, 'largeDesktopSize' => false]);
                        $row->addCell($cell);
                    }
                    Html::addCssClass($subrowSettings, 'list-group-item-split');
                    $parts[] = Html::tag('div', $row->generate(), $subrowSettings);
                }
            } else {
                $this->renderItemContentRow($parts, $fieldName, $settings, $model, $key, $index);
            }
        }

        return implode("", $parts);
    }

    protected function renderItemContentRow (&$parts, $fieldName, $settings, $model, $key, $index)
    {
        $tag = isset($settings['tag']) ? $settings['tag'] : 'div';
        unset($settings['tag']);
        if (is_callable($fieldName)) {
            $value = call_user_func_array($fieldName, [$this, $model, $settings]);
        } else {
            $value = $this->getItemFieldValue($model, $fieldName, $settings);
        }
        if (!empty($value)) {
            $parts[] = Html::tag($tag, $value, $settings);
        }
    }

    public function getListOptions()
    {
        return ['class' => 'list-group'];
    }

    public function getListItemOptions($model, $key, $index)
    {
        return ['class' => 'list-group-item expandable'];
    }

    public function renderItem($model, $key, $index)
    {
        $listItemOptions = $this->getListItemOptions($model, $key, $index);
        $listTag = ArrayHelper::remove($listItemOptions, 'tag', 'li');

        $parts = [];
        $parts[] = $this->renderItemMenu($model, $key, $index);
        $parts[] = $this->renderItemContent($model, $key, $index);

        return Html::tag($listTag, implode('', $parts), $listItemOptions);
    }

    public function renderItemMenu($model, $key, $index)
    {
        $menuItems = $this->getMenuItems($model, $key, $index);
        if (!empty($menuItems)) {
            foreach ($menuItems as &$menuItem) {
                if (isset($menuItem['icon'])) {
                    if (!isset($menuItem['label'])) { $menuItem['label'] = ''; }
                    $menuItem['label'] = '<span class="'.$menuItem['icon'].'"></span>'. $menuItem['label'];
                    unset($menuItem['icon']);
                }
            }

            return ButtonDropdown::widget([
                    'label' => '',
                    'options' => ['class' => 'pull-right'],
                    'encodeLabel' => false,
                    'dropdown' => [
                        'options' => ['class' => 'pull-right'],
                        'encodeLabels' => false,
                        'items' => $menuItems
                    ],
                    'iconOptions' => ['class' => 'fa fa-angle-down']
                ]);
        }

        return null;
    }

    public function generateContent()
    {
        if ($this->lazy && !empty($this->dataProvider->totalCount)) {
            return str_repeat(Html::tag('div', '', ['class' => 'widget-lazy-placeholder']), min($this->dataProvider->totalCount, $this->dataProvider->pagination->pageSize));
        }
        $results = $this->dataProvider;
        if (!empty($results->totalCount) && !empty($results->count)) {
            $models = $this->dataProvider->getModels();
            $keys = $this->dataProvider->getKeys();

            $listOptions = $this->listOptions;
            $listTag = ArrayHelper::remove($listOptions, 'tag', 'ul');

            $rows = [];
            $rows[] = Html::beginTag($listTag, $listOptions);
            foreach (array_values($models) as $index => $model) {
                $rows[] = $this->renderItem($model, $keys[$index], $index);
            }
            $rows[] = Html::endTag($listTag);

            return implode('', $rows);
        } else {
            return Html::tag('div', $this->emptyMessage, ['class' => 'empty-messages']);
        }
    }

    public function generateFooter()
    {
        $footer = '';
        if ($this->renderPager) {
            $pager = $this->renderPager();
            if ($pager) {
                $footer = Html::tag('div', $pager, ['class' => 'panel-footer clearfix']);
            }
        }

        return parent::generateFooter() . $footer;
    }

    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        //\d([get_class($this), $pagination]);
        if ($pagination === false || $pagination->getPageCount() <= 1) {
            return false;
        }
        /** @var LinkPager $class */
        $pager = $this->pagerSettings;
        $class = ArrayHelper::remove($pager, 'class', 'infinite\widgets\LinkPager');
        $pager['pagination'] = $pagination;
        if (!isset($pager['options'])) {
            $pager['options'] = [];
        }
        $pager['maxButtonCount'] = 6;
        Html::addCssClass($pager['options'], 'pagination pull-right');

        return $class::widget($pager);
    }

}
