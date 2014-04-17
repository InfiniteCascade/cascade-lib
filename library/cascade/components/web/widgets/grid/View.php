<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\grid;

use Yii;

use cascade\web\widgets\grid\columns\Data as DataColumn;

use infinite\base\exceptions\Exception;

/**
 * View [@doctodo write class description for View]
 *
 * @author Jacob Morrison <email@ofjacob.com>
**/
class View extends \yii\base\Widget
{
    /**
     * @var __var_widget_type__ __var_widget_description__
     */
    public $widget;
    /**
     * @var __var_state_type__ __var_state_description__
     */
    public $state;
    /**
     * @var __var_dataProvider_type__ __var_dataProvider_description__
     */
    public $dataProvider;
    /**
     * @var __var_emptyText_type__ __var_emptyText_description__
     */
    public $emptyText = 'No items found';
    /**
     * @var __var_htmlOptions_type__ __var_htmlOptions_description__
     */
    public $htmlOptions = [];
    /**
     * @var __var_sortableAttributes_type__ __var_sortableAttributes_description__
     */
    public $sortableAttributes;
    /**
     * @var __var_filters_type__ __var_filters_description__
     */
    public $filters;
    /**
     * @var __var_views_type__ __var_views_description__
     */
    public $views = ['list'];
    /**
     * @var __var_currentView_type__ __var_currentView_description__
     */
    public $currentView = 'list';
    /**
     * @var __var_itemsPerRequest_type__ __var_itemsPerRequest_description__
     */
    public $itemsPerRequest = 20;
    /**
     * @var __var_limit_type__ __var_limit_description__
     */
    public $limit;
    /**
     * @var __var_rendererSettings_type__ __var_rendererSettings_description__
     */
    public $rendererSettings = [];
    /**
     * @var __var_itemMenu_type__ __var_itemMenu_description__
     */
    public $itemMenu = [];
    /**
     * @var __var_additionalClasses_type__ __var_additionalClasses_description__
     */
    public $additionalClasses;
    /**
     * @var __var_specialItemClasses_type__ __var_specialItemClasses_description__
     */
    public $specialItemClasses = [];
    /**
     * @var __var_nullDisplay_type__ __var_nullDisplay_description__
     */
    public $nullDisplay = '';
    /**
     * @var __var__totalItems_type__ __var__totalItems_description__
     */
    protected $_totalItems;
    /**
     * @var __var__currentData_type__ __var__currentData_description__
     */
    protected $_currentData;
    /**
     * @var __var__currentDataRaw_type__ __var__currentDataRaw_description__
     */
    protected $_currentDataRaw;
    /**
     * @var __var__columns_type__ __var__columns_description__
     */
    protected $_columns;
    /**
     * @var __var__columnSettings_type__ __var__columnSettings_description__
     */
    protected $_columnSettings;
    /**
     * @var __var__formatter_type__ __var__formatter_description__
     */
    protected $_formatter;

    /**
    * @inheritdoc
    **/
    public function init()
    {
        if ($this->dataProvider===null) {
            throw new Exception(Yii::t('zii','The "dataProvider" property cannot be empty.'));
        }

        $this->htmlOptions['id']=$this->getId();
        $this->htmlOptions['class']='grid-view';
        $this->_prepareDataProvider();
    }

    /**
    * @inheritdoc
    **/
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
            $options=CJSON::encode($options);
            if (!empty($this->additionalClasses)) {
                $this->htmlOptions['class'] .= ' '. $this->additionalClasses;
            }
            $this->htmlOptions['data-grid-view-options'] = $options;
            echo Html::tag('div', '', $this->htmlOptions);
        }
    }

    /**
     * Get column settings
     * @return __return_getColumnSettings_type__ __return_getColumnSettings_description__
     */
    public function getColumnSettings()
    {
        if (is_null($this->_columnSettings)) {
            $this->_columnSettings = [];
            foreach ($this->columns as $key => $c) {
                if (!$c->visible) { continue; }
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
     * Get data
     * @return __return_getData_type__ __return_getData_description__
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
                if ($this->owner->instanceSettings['whoAmI'] === 'parent' AND isset($r->childObject) AND $r->childObject->hasBehavior('Access')) {
                    $p['acl'] = $r->childObject->aclSummary();
                } elseif ($this->owner->instanceSettings['whoAmI'] === 'child' AND isset($r->parentObject) AND $r->parentObject->hasBehavior('Access')) {
                    $p['acl'] = $r->parentObject->aclSummary();
                } elseif ($r->hasBehavior('Access')) {
                    $p['acl'] = $r->aclSummary();
                }
                $this->_currentData['item-'. $itemNumber] = $p;
                $row++; $itemNumber++;
            }
        }

        return $this->_currentData;
    }

    /**
     * Set columns
     * @param __param_columns_type__     $columns __param_columns_description__
     * @return __return_setColumns_type__ __return_setColumns_description__
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
     * __method_createGridColumn_description__
     * @param __param_text_type__              $text __param_text_description__
     * @return __return_createGridColumn_type__ __return_createGridColumn_description__
     * @throws Exception __exception_Exception_description__
     */
    protected function createGridColumn($text)
    {
        if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new Exception(Yii::t('zii', 'The column must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));
        }
        $column=new DataColumn($this);
        $column->name=$matches[1];
        if (isset($matches[3]) && $matches[3]!=='')
            $column->type=$matches[3];
        if (isset($matches[5]))
            $column->header=$matches[5];

        return $column;
    }

    /**
     * Get columns
     * @return __return_getColumns_type__ __return_getColumns_description__
     */
    public function getColumns()
    {
        if (is_null($this->_columns)) {
            $this->columns = $this->dataProvider->model->attributeNames();
        }

        return $this->_columns;
    }

    /**
     * Get data key
     * @return __return_getDataKey_type__ __return_getDataKey_description__
     */
    public function getDataKey()
    {
        return 'ajax-'. $this->id;
    }

    /**
     * Get total items
     * @return __return_getTotalItems_type__ __return_getTotalItems_description__
     */
    public function getTotalItems()
    {
        if (is_null($this->_totalItems)) {
            $this->_totalItems = $this->dataProvider->totalItemCount;
        }

        return $this->_totalItems;
    }

    /**
     * __method__prepareDataProvider_description__
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
     * Get formatter
     * @return CFormatter the formatter instance. Defaults to the 'format' application component.
     */
    public function getFormatter()
    {
        if ($this->_formatter===null)
            $this->_formatter=Yii::$app->format;

        return $this->_formatter;
    }

    /**
     * Set formatter
     * @param CFormatter $value the formatter instance
     */
    public function setFormatter($value)
    {
        $this->_formatter=$value;
    }
}
