<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\grid;

use cascade\web\widgets\grid\columns\Data as DataColumn;
use infinite\base\exceptions\Exception;
use Yii;

/**
 * View [[@doctodo class_description:cascade\components\web\widgets\grid\View]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class View extends \yii\base\Widget
{
    /**
     * @var [[@doctodo var_type:widget]] [[@doctodo var_description:widget]]
     */
    public $widget;
    /**
     * @var [[@doctodo var_type:state]] [[@doctodo var_description:state]]
     */
    public $state;
    /**
     * @var [[@doctodo var_type:dataProvider]] [[@doctodo var_description:dataProvider]]
     */
    public $dataProvider;
    /**
     * @var [[@doctodo var_type:emptyText]] [[@doctodo var_description:emptyText]]
     */
    public $emptyText = 'No items found';
    /**
     * @var [[@doctodo var_type:htmlOptions]] [[@doctodo var_description:htmlOptions]]
     */
    public $htmlOptions = [];
    /**
     * @var [[@doctodo var_type:sortableAttributes]] [[@doctodo var_description:sortableAttributes]]
     */
    public $sortableAttributes;
    /**
     * @var [[@doctodo var_type:filters]] [[@doctodo var_description:filters]]
     */
    public $filters;
    /**
     * @var [[@doctodo var_type:views]] [[@doctodo var_description:views]]
     */
    public $views = ['list'];
    /**
     * @var [[@doctodo var_type:currentView]] [[@doctodo var_description:currentView]]
     */
    public $currentView = 'list';
    /**
     * @var [[@doctodo var_type:itemsPerRequest]] [[@doctodo var_description:itemsPerRequest]]
     */
    public $itemsPerRequest = 20;
    /**
     * @var [[@doctodo var_type:limit]] [[@doctodo var_description:limit]]
     */
    public $limit;
    /**
     * @var [[@doctodo var_type:rendererSettings]] [[@doctodo var_description:rendererSettings]]
     */
    public $rendererSettings = [];
    /**
     * @var [[@doctodo var_type:itemMenu]] [[@doctodo var_description:itemMenu]]
     */
    public $itemMenu = [];
    /**
     * @var [[@doctodo var_type:additionalClasses]] [[@doctodo var_description:additionalClasses]]
     */
    public $additionalClasses;
    /**
     * @var [[@doctodo var_type:specialItemClasses]] [[@doctodo var_description:specialItemClasses]]
     */
    public $specialItemClasses = [];
    /**
     * @var [[@doctodo var_type:nullDisplay]] [[@doctodo var_description:nullDisplay]]
     */
    public $nullDisplay = '';
    /**
     * @var [[@doctodo var_type:_totalItems]] [[@doctodo var_description:_totalItems]]
     */
    protected $_totalItems;
    /**
     * @var [[@doctodo var_type:_currentData]] [[@doctodo var_description:_currentData]]
     */
    protected $_currentData;
    /**
     * @var [[@doctodo var_type:_currentDataRaw]] [[@doctodo var_description:_currentDataRaw]]
     */
    protected $_currentDataRaw;
    /**
     * @var [[@doctodo var_type:_columns]] [[@doctodo var_description:_columns]]
     */
    protected $_columns;
    /**
     * @var [[@doctodo var_type:_columnSettings]] [[@doctodo var_description:_columnSettings]]
     */
    protected $_columnSettings;
    /**
     * @var [[@doctodo var_type:_formatter]] [[@doctodo var_description:_formatter]]
     */
    protected $_formatter;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->dataProvider === null) {
            throw new Exception(Yii::t('zii', 'The "dataProvider" property cannot be empty.'));
        }

        $this->htmlOptions['id'] = $this->getId();
        $this->htmlOptions['class'] = 'grid-view';
        $this->_prepareDataProvider();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->_prepareDataProvider();
        $data = $this->getData();
        if (!empty($this->state['fetch'])) {
            ob_clean();
            // you need a subset of the data
            Yii::$app->controller->json($data);
            Yii::$app->end();
        } else {
            $columnSettings = $this->getColumnSettings();
            $options = [];
            $options['currentPage'] = $this->dataProvider->pagination->currentPage + 1;
            $options['widget'] = $this->widget;
            $options['state'] = $this->state;
            $options['columns'] = $columnSettings;
            $options['data'] = $data;
            $options['totalItems'] = $this->getTotalItems();
            $options['currentView'] = $this->currentView;
            $options['views'] = $this->views;
            $options['itemMenu'] = $this->itemMenu;
            $options['loadMore'] = is_null($this->limit);
            $options['emptyText'] = $this->emptyText;
            $options['specialItemClasses'] = $this->specialItemClasses;
            $options['rendererSettings'] = $this->rendererSettings;
            $options['rendererSettings']['grid']['sortableLabel'] = 'Sort by:';
            $options['rendererSettings']['grid']['sortable'] = $this->sortableAttributes;
            $options = CJSON::encode($options);
            if (!empty($this->additionalClasses)) {
                $this->htmlOptions['class'] .= ' ' . $this->additionalClasses;
            }
            $this->htmlOptions['data-grid-view-options'] = $options;
            echo Html::tag('div', '', $this->htmlOptions);
        }
    }

    /**
     * Get column settings.
     *
     * @return [[@doctodo return_type:getColumnSettings]] [[@doctodo return_description:getColumnSettings]]
     */
    public function getColumnSettings()
    {
        if (is_null($this->_columnSettings)) {
            $this->_columnSettings = [];
            foreach ($this->columns as $key => $c) {
                if (!$c->visible) {
                    continue;
                }
                $this->_columnSettings[$key] = ['label' => $c->getDataLabel()];

                if (!isset($c->htmlOptions)) {
                    $c->htmlOptions = [];
                }
                $this->_columnSettings[$key]['htmlOptions'] = $c->htmlOptions;
                $sortableResolve = $this->dataProvider->sort->resolveAttribute($c->name);
                $this->_columnSettings[$key]['sortable'] = !empty($sortableResolve);
            }
        }

        return $this->_columnSettings;
    }

    /**
     * Get data.
     *
     * @return [[@doctodo return_type:getData]] [[@doctodo return_description:getData]]
     */
    public function getData()
    {
        if (is_null($this->_currentData)) {
            $this->_currentDataRaw = $this->dataProvider->getData();
            $this->_currentData = [];
            $itemNumber = $this->dataProvider->pagination->offset;
            $row = 0;
            foreach ($this->_currentDataRaw as $r) {
                $p = ['itemNumber' => $itemNumber, 'id' => $r->primaryKey, 'values' => []];
                foreach ($this->columns as $key => $c) {
                    $p['values'][$key] = $c->getDataValue($row, $r, false);
                }
                $p['acl'] = [];
                if ($this->owner->instanceSettings['whoAmI'] === 'parent' and isset($r->childObject) and $r->childObject->hasBehavior('Access')) {
                    $p['acl'] = $r->childObject->aclSummary();
                } elseif ($this->owner->instanceSettings['whoAmI'] === 'child' and isset($r->parentObject) and $r->parentObject->hasBehavior('Access')) {
                    $p['acl'] = $r->parentObject->aclSummary();
                } elseif ($r->hasBehavior('Access')) {
                    $p['acl'] = $r->aclSummary();
                }
                $this->_currentData['item-' . $itemNumber] = $p;
                $row++;
                $itemNumber++;
            }
        }

        return $this->_currentData;
    }

    /**
     * Set columns.
     *
     * @param [[@doctodo param_type:columns]] $columns [[@doctodo param_description:columns]]
     *
     * @return [[@doctodo return_type:setColumns]] [[@doctodo return_description:setColumns]]
     */
    public function setColumns($columns)
    {
        $this->_columns = [];
        foreach ($columns as $key => $columnName) {
            if (is_array($columnName)) {
                $settings = $columnName;
                $settings['name'] = $key;
            } else {
                $settings = ['name' => $columnName];
            }
            if (!isset($settings['class'])) {
                $settings['class'] = '\cascade\web\widgets\grid\columns\Data';
            }
            if (!isset($settings['value'])) {
                $settings['type'] = 'raw';
                $settings['value'] = function ($data, $row) use ($settings) {
                    $key = explode('.', $settings['name']);
                    $object = $data;
                    while (count($key) > 1) {
                        $next = array_shift($key);
                        if (is_object($object)) {
                            $object = $object->{$next};
                        } else {
                            $object = $object[$next];
                        }
                    }
                    $key = $key[0];
                    if (is_object($object)) {
                        $model = get_class($object);
                        $fields = $model::getFields($object);
                        if (isset($fields[$key])) {
                            return $fields[$key]->getFormattedValue();
                        }
                    }

                    return $object->{$key};
                };
            }
            if (!isset($settings['type'])) {
                $settings['type'] = 'raw';
            }
            $column = Yii::createObject($settings, $this);
            $key = $column->name;

            if (!$column->visible) {
                //	continue;
            }
            $this->_columns[$key] = $column;
        }
    }

    /**
     * [[@doctodo method_description:createGridColumn]].
     *
     * @param [[@doctodo param_type:text]] $text [[@doctodo param_description:text]]
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:createGridColumn]] [[@doctodo return_description:createGridColumn]]
     *
     */
    protected function createGridColumn($text)
    {
        if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new Exception(Yii::t('zii', 'The column must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));
        }
        $column = new DataColumn($this);
        $column->name = $matches[1];
        if (isset($matches[3]) && $matches[3] !== '') {
            $column->type = $matches[3];
        }
        if (isset($matches[5])) {
            $column->header = $matches[5];
        }

        return $column;
    }

    /**
     * Get columns.
     *
     * @return [[@doctodo return_type:getColumns]] [[@doctodo return_description:getColumns]]
     */
    public function getColumns()
    {
        if (is_null($this->_columns)) {
            $this->columns = $this->dataProvider->model->attributeNames();
        }

        return $this->_columns;
    }

    /**
     * Get data key.
     *
     * @return [[@doctodo return_type:getDataKey]] [[@doctodo return_description:getDataKey]]
     */
    public function getDataKey()
    {
        return 'ajax-' . $this->id;
    }

    /**
     * Get total items.
     *
     * @return [[@doctodo return_type:getTotalItems]] [[@doctodo return_description:getTotalItems]]
     */
    public function getTotalItems()
    {
        if (is_null($this->_totalItems)) {
            $this->_totalItems = $this->dataProvider->totalItemCount;
        }

        return $this->_totalItems;
    }

    /**
     * [[@doctodo method_description:_prepareDataProvider]].
     */
    protected function _prepareDataProvider()
    {
        if (!is_null($this->limit)) {
            $this->dataProvider->pagination->pageSize = $this->limit;
        } else {
            $this->dataProvider->pagination->pageSize = $this->itemsPerRequest;
        }
    }

    /**
     * Get formatter.
     *
     * @return CFormatter the formatter instance. Defaults to the 'format' application component.
     */
    public function getFormatter()
    {
        if ($this->_formatter === null) {
            $this->_formatter = Yii::$app->format;
        }

        return $this->_formatter;
    }

    /**
     * Set formatter.
     *
     * @param CFormatter $value the formatter instance
     */
    public function setFormatter($value)
    {
        $this->_formatter = $value;
    }
}
