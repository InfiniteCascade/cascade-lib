<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use Yii;

use cascade\models\Relation;
use cascade\models\KeyTranslation;

use infinite\helpers\ArrayHelper;
use cascade\components\dataInterface\connectors\generic\Model as GenericModel;

/**
 * DataSource [@doctodo write class description for DataSource]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataSource extends \cascade\components\dataInterface\connectors\generic\DataSource
{
    /**
     * @inheritdoc
     */
    public $fieldMapClass = 'cascade\\components\\dataInterface\\connectors\\db\\FieldMap';
    /**
     * @inheritdoc
     */
    public $dataItemClass = 'cascade\\components\\dataInterface\\connectors\\db\\DataItem';
    /**
     * @inheritdoc
     */
    public $searchClass = 'cascade\\components\\dataInterface\\connectors\\db\\Search';


    /**
     * Get foreign data model
     * @param __param_key_type__                  $key __param_key_description__
     * @return __return_getForeignDataModel_type__ __return_getForeignDataModel_description__
     */
    public function getForeignDataModel($key)
    {
        $config = $this->settings['foreignPullParams'];
        if (!isset($config['where'])) {
            $config['where'] = [];
        }
        if (!empty($config['where'])) {
            $config['where'] = ['and', $config['where'], [$this->foreignModel->primaryKey() => $key]];
        } else {
            $config['where'][$this->foreignModel->primaryKey()] = $key;
        }
        //var_dump($this->foreignModel->find($config)->count('*', $this->module->db));
        return $this->foreignModel->findOne($config);
    }



    /**
     * Get unmapped foreign keys
     * @return __return_getUnmappedForeignKeys_type__ __return_getUnmappedForeignKeys_description__
     */
    public function getUnmappedForeignKeys()
    {
        $mappedForeign = ArrayHelper::getColumn($this->_map, 'foreignKey');
        $u = array_diff(array_keys($this->foreignModel->meta->schema->columns), $mappedForeign);
        unset($u[$this->foreignPrimaryKeyName]);

        return $u;
    }

    /**
     * __method_generateKey_description__
     * @param cascade\components\dataInterface\connectors\db\Model $foreignObject __param_foreignObject_description__
     * @return __return_generateKey_type__                          __return_generateKey_description__
     */
    public function generateKey(GenericModel $foreignObject)
    {
        if (is_null($this->keyGenerator)) {
            $self = $this;
            $this->keyGenerator = function ($foreignModel) use ($self) {
                return [$self->module->systemId, $foreignModel->tableName, $foreignModel->primaryKey];
            };
        }
        $keyGen = $this->keyGenerator;
        $return = $keyGen($foreignObject);

        if (isset($return)) {
            if (is_array($return)) {
                $return = implode('.', $return);
            }

            return $return;
        }

        return null;
    }

    
    /**
     * __method_loadForeignDataItems_description__
     */
    protected function loadForeignDataItems()
    {
        $this->_foreignDataItems = [];
        if ($this->lazyForeign) {
            $primaryKeys = $this->foreignModel->findPrimaryKeys($this->settings['foreignPullParams']);
            foreach ($primaryKeys as $primaryKey) {
                $this->createForeignDataItem(null, ['foreignPrimaryKey' => $primaryKey]);
            }
        } else {
            $foreignModels= $this->foreignModel->findAll($this->settings['foreignPullParams']);
            foreach ($foreignModels as $key => $model) {
                $this->createForeignDataItem($model, []);
            }
        }

    }
}
