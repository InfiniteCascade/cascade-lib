<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\module\Generator $generator
 */
$className = $generator->moduleClass;
$pos = strrpos($className, '\\');
$ns = ltrim(substr($className, 0, $pos), '\\');
$className = substr($className, $pos + 1);

echo "<?php\n";
?>

namespace <?=$generator->moduleNamespace; ?>;

use Yii;

class Module extends \cascade\components\types\Module
{
	protected $_title = '<?= $generator->title; ?>';
	public $icon = '<?= $generator->icon; ?>';
	public $hasDashboard = <?php echo empty($generator->hasDashboard) ? 'false' : 'true'; ?>;
	public $uniparental = <?php echo empty($generator->uniparental) ? 'false' : 'true'; ?>;
	public $priority = <?= $generator->priority; ?>;

	public $widgetNamespace = '<?=$generator->getWidgetNamespace(); ?>';
	public $modelNamespace = '<?=$generator->getModelNamespace(); ?>';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		
		Yii::$app->registerMigrationAlias('<?= $generator->migrationsAlias; ?>');
	}

	/**
	 * @inheritdoc
	 */
	public function defaultRelationshipSettings()
	{
		return array_merge(parent::defaultRelationshipSettings(), [<?php 
		$parts = [];
		if ($generator->uniqueParent) {
			$parts[] = "'uniqueParent' => true";
		}
		if ($generator->uniqueChild) {
			$parts[] = "'uniqueChild' => true";
		}
		if (!empty($parts)) {
			echo "\n\t\t\t";
			echo implode(",\n\t\t\t", $parts);
			echo "\n\t\t";
		}
		?>]);
	}

	/**
	 * @inheritdoc
	 */
	public function widgets()
	{
	<?php
	if (!empty($generator->section)) {
		echo "\t\$widgets = parent::widgets();\n";
		foreach ($generator->widgets as $widget) {
			echo "\t\t\$widgets['{$widget}']['section'] = Yii::\$app->collectors['sections']->getOne('{$generator->section}');\n";
		}
		echo "\t\treturn \$widgets;";
	} else {
		echo "\t\treturn parent::widgets();";
	}
	?>

	}

	
	/**
	 * @inheritdoc
	 */
	public function parents()
	{
		return [<?php
			if (!empty($generator->parents)) {
				echo "\n";
				foreach(explode(',', $generator->parents) as $parent) {
					$parent = trim($parent);
					echo "\t\t\t'{$parent}' => [],\n";
				}
			}
		?>
		];
	}

	
	/**
	 * @inheritdoc
	 */
	public function children()
	{
		return [<?php
			if (!empty($generator->children)) {
				echo "\n";
				foreach(explode(',', $generator->children) as $child) {
					$child = trim($child);
					echo "\t\t\t'{$child}' => [],\n";
				}
			}
		?>];
	}

	
	/**
	 * @inheritdoc
	 */
	public function taxonomies()
	{
		return [];
	}
}