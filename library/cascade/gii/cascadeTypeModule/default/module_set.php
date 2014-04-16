<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name=>label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name=>relation declaration)
 */

echo "<?php\n";
?>
namespace <?= $generator->getBaseNamespace() ?>;

use Yii;

class Extension extends \cascade\components\base\ModuleSetExtension
{
    public static function init()
    {
        parent::init();
        Yii::setAlias('@<?= str_replace('\\', '/', $generator->getBaseNamespace()) ?>', __DIR__);
    }

    public static function getModules()
    {
        $m = [];
        <?= $generator->getModuleSetModules() ?>

        return $m;
    }
}
