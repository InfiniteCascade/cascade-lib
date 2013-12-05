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
	public $title = '<?= $generator->title; ?>';
	public $icon = '<?= $generator->icon; ?>';
	public $uniparental = <?php echo empty($generator->uniparental) ? 'false' : 'true'; ?>;
	public $hasDashboard = <?php echo empty($generator->hasDashboard) ? 'false' : 'true'; ?>;

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
	public function widgets()
	{
		return parent::widgets();
	}

	
	/**
	 * @inheritdoc
	 */
	public function parentSettings()
	{
		$settings = parent::parentSettings();
		$settings['title'] = false;
		$settings['showDescriptor'] = true;
		$settings['allow'] = <?php if ($generator->independent) { echo '2'; } else { echo '1'; } ?>;
		return $settings;
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
					echo "\t\t\t'{$child}' => ['uniqueChild' => true],\n";
				}
			}
		?>
		];
	}

	
	/**
	 * @inheritdoc
	 */
	public function taxonomies()
	{
		return [];
	}
}
