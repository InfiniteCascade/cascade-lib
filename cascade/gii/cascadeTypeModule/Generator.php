<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cascade\gii\cascadeTypeModule;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;


use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\helpers\Inflector;

/**
 * This generator will generate the skeleton code needed for a 
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @since 1.0
 */


class Generator extends \yii\gii\Generator
{
	public $db = 'db';
	//public $moduleName;
	public $baseNamespace = 'cascade\modules';
	public $baseClass = 'cascade\components\types\ActiveRecord';
	
	public $title;
	public $uniparental = 0;
	public $hasDashboard = 1;
	public $priority = 1;
	public $icon;

	public $widgets = [];


	public $migrationTimestamp;

	public $generateRelations = true;
	public $generateLabelsFromComments = false;

	public $tableName;
	public $descriptorField;
	public $children = '';
	public $parents = '';
	public $section;

	public function __construct() {
		if (is_null($this->migrationTimestamp)) {
			$this->migrationTimestamp = time();
		}
		return parent::__construct();
	}

	public function getModelClass() {
		return $this->generateClassName($this->tableName);
	}

	public function getModuleName() {
		return preg_replace('/^Object/', 'Type', $this->modelClass);
	}

	public function getModuleNamespace() {
		return $this->baseNamespace . '\\' . $this->moduleName;
	}

	public function getModuleClass() {
		return $this->moduleNamespace  .'\\' . 'Module';
	}

	public function getModuleID() {
		return $this->moduleName;
	}

	public function getModuleSystemID() {
		return preg_replace('/^Type/', '', $this->moduleName);
	}

	// model
	public function getNs() {
		return $this->baseNamespace . '\\models';
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Cascade Object Type Module Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator helps you to generate the skeleton code needed by a Cascade object type module.';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			/* Module */
			//['moduleID, moduleClass, baseNamespace', 'filter', 'filter' => 'trim'],
			//['moduleID, moduleClass, baseNamespace', 'required'],
			//['moduleID', 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'],
			//['moduleClass, baseNamespace', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
			//['moduleClass', 'validateModuleClass'],

			/* Model */
			//['modelClass', 'filter', 'filter' => 'trim'],
			[['tableName', 'icon', 'title'], 'required'],
			//['db, modelClass', 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
			// ['ns, baseClass', 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
			[['tableName'], 'match', 'pattern' => '/^(\w+\.)?([\w\*]+)$/', 'message' => 'Only word characters, and optionally an asterisk and/or a dot are allowed.'],
			//['db', 'validateDb'],
			//['ns', 'validateNamespace'],
			[['tableName'], 'validateTableName'],
			[['migrationTimestamp'], 'integer'],
			[['section', 'descriptorField', 'parents', 'children', 'uniparental', 'hasDashboard', 'baseNamespace'], 'safe'],
			//['baseClass', 'validateClass', 'params' => ['extends' => ActiveRecord::className()]],
			//['generateRelations, generateLabelsFromComments', 'boolean'],

		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'baseNamespace' => 'Base Namespace',

			/* Module */
			'moduleID' => 'Module ID',
			'moduleClass' => 'Module Class',

			'uniparental' => 'Allow only one parent',
			'hasDashboard' => 'Managed by its own dashboard',

			/* Model */

			'ns' => 'Namespace',
			'db' => 'Database Connection ID',
			'tableName' => 'Table Name',
			'modelClass' => 'Model Class',
			'baseClass' => 'Base Class',
			'generateRelations' => 'Generate Relations',
			'generateLabelsFromComments' => 'Generate Labels from DB Comments',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return [
			/* Module */
			'moduleID' => 'This refers to the ID of the module, e.g., <code>admin</code>.',
			'moduleClass' => 'This is the fully qualified class name of the module, e.g., <code>cascade\modules\admin\Module</code>.',

			'title' => 'Single noun for this object type',
			'section' => 'Which section does this widget fall into?',
			'uniparental' => 'Objects of this type can only have one parent.',
			'hasDashboard' => 'Objects of this type are managed from their own dashboard.',

			/* Model */

			'ns' => 'This is the namespace of the ActiveRecord class to be generated, e.g., <code>cascade\models</code>',
			'db' => 'This is the ID of the DB application component.',
			'tableName' => 'This is the name of the DB table that the new ActiveRecord class is associated with, e.g. <code>tbl_post</code>.
				The table name may consist of the DB schema part if needed, e.g. <code>public.tbl_post</code>.
				The table name may contain an asterisk to match multiple table names, e.g. <code>tbl_*</code>
				will match tables who name starts with <code>tbl_</code>. In this case, multiple ActiveRecord classes
				will be generated, one for each matching table name; and the class names will be generated from
				the matching characters. For example, table <code>tbl_post</code> will generate <code>Post</code>
				class.',
			'modelClass' => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
				the namespace part as it is specified in "Namespace". You do not need to specify the class name
				if "Table Name" contains an asterisk at the end, in which case multiple ActiveRecord classes will be generated.',
			'baseClass' => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
			'generateRelations' => 'This indicates whether the generator should generate relations based on
				foreign key constraints it detects in the database. Note that if your database contains too many tables,
				you may want to uncheck this option to accelerate the code generation proc	ess.',
			'generateLabelsFromComments' => 'This indicates whether the generator should generate attribute labels
				by using the comments of the corresponding DB columns.',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function successMessage()
	{
		if (Yii::$app->hasModule($this->moduleID)) {
			$link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($this->moduleID), ['target' => '_blank']);
			return "The module has been generated successfully. You may $link.";
		}

		$output = <<<EOD
<p>The module has been generated successfully.</p>
<p>To access the module, you need to add this to your application configuration:</p>
EOD;
		$code = <<<EOD
<?php
	......
	'modules' => [
		'{$this->moduleID}' => [
			'class' => '{$this->moduleClass}',
		],
	],
	......
EOD;

		return $output . '<pre>' . highlight_string($code, true) . '</pre>';
	}

	/**
	 * @inheritdoc
	 */
	public function requiredTemplates()
	{
		return ['module.php', 'detail_list_widget.php', 'simple_link_list_widget.php',  'model.php', 'migration.php'];
	}

	/**
	 * @inheritdoc
	 */
	public function autoCompleteData()
	{
		return [
			'tableName' => function () {
				return $this->getDbConnection()->getSchema()->getTableNames();
			},
		];
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return ['baseNamespace'];
	}


	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = [];
		$modulePath = $this->getModulePath();
		$widgets = [];
		$systemId = ucfirst($this->getModuleSystemID());
		if (!empty($this->section) && substr($this->section, 0, 1) === '_') {
			if ($this->section === '_side') {
				$files[] = new CodeFile(
					$modulePath . '/widgets/EmbeddedList.php',
					$this->render("side_list_widget.php")
				);
			} else {
				$files[] = new CodeFile(
					$modulePath . '/widgets/EmbeddedList.php',
					$this->render("embedded_list_widget.php")
				);
			}
			$widgets[] = "Embedded{$systemId}Browse";
		} else {
			if (!empty($this->children)) {
				$widgets[] = "Children{$systemId}Browse";
			}
			if (!empty($this->parents)) {
				$widgets[] = "Parents{$systemId}Browse";
			}
			$files[] = new CodeFile(
				$modulePath . '/widgets/DetailList.php',
				$this->render("detail_list_widget.php")
			);
		}
		if (!empty($this->hasDashboard)) {
			$files[] = new CodeFile(
				$modulePath . '/widgets/SimpleLinkList.php',
				$this->render("simple_link_list_widget.php")
			);
			$widgets[] = "{$systemId}Summary";
		}

		$this->widgets = $widgets;

		$files[] = new CodeFile(
			$modulePath . '/Module.php',
			$this->render("module.php")
		);
		$relations = $this->generateRelations();
		$db = $this->getDbConnection();
		foreach ($this->getTableNames() as $tableName) {
			$className = $this->generateClassName($tableName);
			$tableSchema = $db->getTableSchema($tableName);
			$myRelations = isset($relations[$className]) ? $relations[$className] : [];
			$params = [
				'tableName' => $tableName,
				'className' => $className,
				'tableSchema' => $tableSchema,
				'descriptorField' => $this->generateDescriptorField($tableSchema),
				'labels' => $this->generateLabels($tableSchema),
				'rules' => $this->generateRules($tableSchema),
				'relations' => $myRelations,
				'uses' => $this->generateRelationUses($myRelations),
				'migrationClassName' => $this->migrationClassName,
				'migrationsNamespace' => $this->migrationsNamespace,
				'createTableSyntax' => $this->generateCreateTableColumns($tableSchema),
				'createTableIndices' => $this->generateTableIndices($tableSchema),
				'columnSettingSkel' => $this->generateColumnSettings($tableSchema)
			];
			$files[] = new CodeFile(
				Yii::getAlias('@' . str_replace('\\', '/', $this->modelNamespace)) . '/' . $className . '.php',
				$this->render('model.php', $params)
			);
			$files[] = $migration = new CodeFile(
				$this->getMigrationPath(),
				$this->render('migration.php', $params)
			);
		}
		return $files;
	}

	/**
	 * Validates [[moduleClass]] to make sure it is a fully qualified class name.
	 */
	public function validateModuleClass()
	{
		if (strpos($this->moduleClass, '\\') === false || Yii::getAlias('@' . str_replace('\\', '/', $this->moduleClass)) === false) {
			$this->addError('moduleClass', 'Module class must be properly namespaced.');
		}
		if (substr($this->moduleClass, -1, 1) == '\\') {
			$this->addError('moduleClass', 'Module class name must not be empty. Please enter a fully qualified class name. e.g. "cascade\\modules\\admin\\Module".');
		}
	}

	public function generateDescriptorField($table) {
		$field = '';
		if (!empty($this->descriptorField)) {
			if (strpos($this->descriptorField, ',') === false) {
				$field = "\tpublic \$descriptorField = '{$this->descriptorField}';\n";
			} else {
				$parts = explode(',', str_replace(' ', '', $this->descriptorField));
				$field = "\tpublic \$descriptorField = ['".implode("', '", $parts)."'];\n";
			}
		} else {
			$check = ['name', 'title'];
			foreach ($check as $column) {
				if (isset($table->columns[$column])) {
					$field = "\tpublic \$descriptorField = '$column';\n";
				}
			}
		}
		return $field;
	}

	public function possibleSections() {
		$s = ['' => '(self)'];
		foreach (Yii::$app->collectors['sections']->getAll() as $section) {
			$s[$section->systemId] = $section->object->sectionTitle;
		}
		return $s;
	}
	public function possibleIcons() {
		$path = Yii::getAlias("@vendor/fortawesome/font-awesome/src/icons.yml");
		$data = \Spyc::YAMLLoad($path);
		$icons = [];
		ArrayHelper::multisort($data['icons'], 'name');
		foreach ($data['icons'] as $icon) {
			$group = $icon['categories'][0];
			if (!isset($icons[$group])) { $icons[$group] = []; }
			$icons[$group]['fa fa-'.$icon['id']] = $icon['name'] ."&#09;". '&#x'.$icon['unicode'] . ';';
		}
		ksort($icons);
		return $icons;
	}

	/**
	 * @return boolean the directory that contains the module class
	 */
	public function getModulePath()
	{
		return Yii::getAlias('@' . str_replace('\\', '/', $this->moduleNamespace));
	}

	/**
	 * @return string the widget namespace of the module.
	 */
	public function getWidgetNamespace()
	{
		return $this->moduleNamespace . '\widgets';
	}


	/**
	 * @return string the model namespace of the module.
	 */
	public function getModelNamespace()
	{
		return $this->moduleNamespace . '\models';
	}

	/**
	 * @return string the model namespace of the module.
	 */
	public function getMigrationsNamespace()
	{
		return $this->moduleNamespace . '\migrations';
	}

	public function getMigrationPath() {
		return Yii::getAlias('@' . str_replace('\\', '/', $this->migrationsNamespace)) . '/' . $this->migrationClassName . '.php';
	}

	public function getMigrationDirectory() {
		return Yii::getAlias('@' . str_replace('\\', '/', $this->migrationsNamespace));
	}
	/**
	 * @return string the model namespace of the module.
	 */
	public function getMigrationsAlias()
	{
		return '@' . str_replace('\\', '/', $this->migrationsNamespace);
	}

	/**
	 * @return string the model namespace of the module.
	 */
	public function getMigrationClassName()
	{
		$postfix = '_initial_'.$this->tableName;
		if (is_dir($this->migrationDirectory)) {
			$searchExisting = FileHelper::findFiles($this->migrationDirectory, ['only' => [$postfix.'.php']]);
			if (!empty($searchExisting)) {
				return strstr(basename($searchExisting[0]), '.php', true);
			}
		}
		return  'm' . gmdate('ymd_His', $this->migrationTimestamp) . $postfix;
	}


	/**
	 * Collects the foreign key column details for the given table.
	 * @param TableSchema $table the table metadata
	 */
	protected function findTableKeys($table)
	{
		$r = [
				'foreignKeys' => [],
				'indices' => [],
				'primaryKeys' => []
			];
		$row = $this->dbConnection->createCommand('SHOW CREATE TABLE ' . $this->dbConnection->getSchema()->quoteSimpleTableName($table->name))->queryOne();
		if (isset($row['Create Table'])) {
			$sql = $row['Create Table'];
		} else {
			$row = array_values($row);
			$sql = $row[1];
		}
		$regexp = '/(PRIMARY KEY)\s+\(([^\)]+)\)/mi';
		if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$pks = array_map('trim', explode(',', str_replace('`', '', $match[2])));
				$r['primaryKeys'][] = ['keys' => $pks];
			}
		}

		$regexp = '/(UNIQUE KEY|INDEX|KEY)\s+([^\s]+)\s+\(([^\)]+)\)/mi';
		if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$unique = $match[1] === 'UNIQUE KEY';
				$fname = trim(str_replace('`', '', $match[2]));
				$pks = array_map('trim', explode(',', str_replace('`', '', $match[3])));
				$r['indices'][$fname] = ['keys' => $pks, 'unique' => $unique];
			}
		}

		$regexp = '/CONSTRAINT\s+([^\s]+)\sFOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)\s+ON DELETE\s+((?:(?!\sON).)*)\s+ON UPDATE\s+((?:(?!,).)*)/mi';
		if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$fname = trim(str_replace('`', '', $match[1]));
				$fks = array_map('trim', explode(',', str_replace('`', '', $match[2])));
				$pks = array_map('trim', explode(',', str_replace('`', '', $match[4])));
				$constraint = ['name' => $fname, 'table' => str_replace('`', '', $match[3]), 'keys' => []];
				$constraint['onDelete'] = trim($match[5]);
				$constraint['onUpdate'] = trim($match[6]);
				foreach ($fks as $k => $name) {
					$constraint['keys'][$name] = $pks[$k];
				}
				$r['foreignKeys'][] = $constraint;
			}
		}

		return $r;
	}

	public function getPrimaryKeyLocation($table) {
		// if multiple, put the primary key in the indicies section
		$count = 0;
		foreach ($table->columns as $column) {
			if ($column->isPrimaryKey) { $count++; }
			if ($count > 1) { return 'index'; }
		}

		return 'table_build';
	}

	public function generateTableIndices($table) {
		$tableName = $table->name;
		$meta = $this->findTableKeys($table);
		$niceName = lcfirst(Inflector::id2camel($tableName, '_'));

		if ($this->getPrimaryKeyLocation($table) === 'index') {
			foreach ($meta['primaryKeys'] as $name => $parts) {
				$keys = $parts['keys'];
				$unique = !empty($parts['unique']);
				$i[] = "\$this->addPrimaryKey('{$niceName}Pk', '{$tableName}', '".implode(',', $keys)."');";
			}
		}

		foreach ($meta['indices'] as $name => $parts) {
			$keys = $parts['keys'];
			$name = $this->fixIndexName($name, $table, $keys);
			$unique = !empty($parts['unique']);
			$i[] = "\$this->createIndex('{$name}', '{$tableName}', '".implode(',', $keys)."', ". ($unique ? 'true' : 'false') .");";
		}

		foreach ($meta['foreignKeys'] as $fk) {
			$keys = $fk['keys'];
			$unique = !empty($fk['unique']);
			$name = $this->fixIndexName($fk['name'], $table, array_values($keys));
			$i[] = "\$this->addForeignKey('{$fk['name']}', '{$tableName}', '".implode(',', array_keys($fk['keys']))."', '{$fk['table']}', '".implode(',', array_values($fk['keys'])) ."', '{$fk['onDelete']}', '{$fk['onUpdate']}');";
		}
		return implode("\n\t\t", $i);
	}

	public function fixIndexName($name, $table, $keys) {
		if (strpos($name, '_') === false) { return $name; }
		$niceName = preg_replace('/object/', '', $table->name);
		$niceName = lcfirst(Inflector::id2camel($niceName, '_'));
		$indices = [];
		foreach ($keys as $key) {
			$indices[] = Inflector::id2camel(substr($key, 0, strpos($key, '_id')), '_');
		}
		return $niceName . implode('', $indices);
	}

	/**
	 * Generates Create table schema
	 * @param \yii\db\TableSchema $table the table schema
	 * @return array the generated validation rules
	 */
	public function generateCreateTableColumns($table)
	{
		$fields = [];
		$queryBuilder = $this->getDbConnection()->getQueryBuilder();
		foreach ($table->columns as $column) {
			$nullExtra = $signedExtra = $defaultExtra = $primaryKeyExtra  = $autoIncrementExtra = '';
			$size = $column->size;
			if (!is_null($column->scale)) {
				$size .= ','. $column->scale;
			}


			if (!$column->allowNull) {
				$nullExtra = ' NOT NULL';
			}

			if ($column->unsigned) {
				$signedExtra = ' unsigned';
			}
			if ($column->autoIncrement) {
				$autoIncrementExtra = ' AUTO_INCREMENT';
			}

			if ($column->isPrimaryKey AND $this->getPrimaryKeyLocation($table) === 'table_build') {
				$primaryKeyExtra = ' PRIMARY KEY';
			}

			if (!empty($column->enumValues)) {
					$size = '\'' . implode('\',\'', $column->enumValues) . '\'';
			}

			$stringValue = $column->typecast($column->defaultValue);
			if (is_null($stringValue)) {
				if ($column->allowNull) {
					$stringValue = 'NULL';
				} else {
					$stringValue = false;
				}
			} elseif (is_bool($stringValue)) {
				$stringValue = ($stringValue) ? 1 : 0;
			} else {
				$stringValue = $this->getDbConnection()->getSchema()->quoteValue($stringValue);
			}
			if ($stringValue !== false) {
				$defaultExtra = ' DEFAULT '. addslashes($stringValue);
			}

			$field = "'{$column->name}' => '";

			// \infinite\base\Debug::d($column);exit;
			$fieldType = $column->dbType;
			preg_match('/^(\w+)(\((.+?)\))?\s*(.+)$/', $fieldType, $fieldTypeParts);
			if (!isset($fieldTypeParts[1])) {
				var_dump($fieldTypeParts);
				var_dump($column);exit;
			}
			$fieldTypeBare = $fieldTypeParts[1];
			// if (isset($fieldTypeBare[4]) AND $fieldTypeBare[4] === 'unsigned') {
			// 	$signedExtra = ' unsigned';
			// }

			if ($fieldType === 'char(36)') {
				$fieldType = $column->dbType .' CHARACTER SET ascii COLLATE ascii_bin';
			} elseif ($fieldType === 'tinyint(1)') {
				$fieldType = 'boolean';
			} elseif(isset($this->getDbConnection()->getSchema()->typeMap[$fieldTypeBare])) {
				$fieldType = $this->getDbConnection()->getSchema()->typeMap[$fieldTypeBare];
				$qb = $this->getDbConnection()->getQueryBuilder();
				if (isset($qb->typeMap[$fieldType])) {
					preg_match('/^(\w+)(\((.+?)\))?\s*(.+)$/', $qb->typeMap[$fieldType], $baseTypeParts);
					if (isset($fieldTypeParts[4]) && isset($baseTypeParts[4]) && $fieldTypeParts[4] !== $baseTypeParts[4]) {
						$size = preg_replace('/[^0-9\,]/', '', $fieldTypeParts[4]);
					}
				}
				if ($size !== false) {
					$fieldType .= '('. $size .')';
				}
			}

			$fieldComplete = trim($fieldType . $signedExtra . $nullExtra . $defaultExtra . $autoIncrementExtra . $primaryKeyExtra);
			$field .= $fieldComplete .'\'';

			$fields[] = $field;
		}
		return implode(",\n\t\t\t", $fields);
	}

	/**
	 * Generates the attribute labels for the specified table.
	 * @param \yii\db\TableSchema $table the table schema
	 * @return array the generated attribute labels (name => label)
	 */
	public function generateLabels($table)
	{
		$labels = [];
		foreach ($table->columns as $column) {
			if ($this->generateLabelsFromComments && !empty($column->comment)) {
				$labels[$column->name] = $column->comment;
			} elseif (!strcasecmp($column->name, 'id')) {
				$labels[$column->name] = 'ID';
			} else {
				$label = Inflector::camel2words($column->name);
				if (strcasecmp(substr($label, -3), ' id') === 0) {
					$label = substr($label, 0, -3) . ' ID';
				}
				$labels[$column->name] = $label;
			}
		}
		return $labels;
	}

	public function generateColumnSettings($table)
	{
		$types = [];
		foreach ($table->columns as $column) {
			if (in_array($column->name, array('id', 'created', 'modified', 'archived', 'deleted')) || strstr($column->name, '_id') !== false) { continue; }
			$types[] = "'{$column->name}' => []";
		}

		return $types;
	}

	/**
	 * Generates validation rules for the specified table.
	 * @param \yii\db\TableSchema $table the table schema
	 * @return array the generated validation rules
	 */
	public function generateRules($table)
	{
		$types = [];
		$lengths = [];
		foreach ($table->columns as $column) {
			if ($column->autoIncrement) {
				continue;
			}
			if (!$column->allowNull && $column->defaultValue === null && !$column->isPrimaryKey) {
				$types['required'][] = $column->name;
			}
			switch ($column->type) {
				case Schema::TYPE_SMALLINT:
				case Schema::TYPE_INTEGER:
				case Schema::TYPE_BIGINT:
					$types['integer'][] = $column->name;
					break;
				case Schema::TYPE_BOOLEAN:
					$types['boolean'][] = $column->name;
					break;
				case Schema::TYPE_FLOAT:
				case Schema::TYPE_DECIMAL:
				case Schema::TYPE_MONEY:
					$types['number'][] = $column->name;
					break;
				case Schema::TYPE_DATE:
				case Schema::TYPE_TIME:
				case Schema::TYPE_DATETIME:
				case Schema::TYPE_TIMESTAMP:
					if (!in_array($column->name, ['created', 'deleted', 'modified'])) {
						$types['safe'][] = $column->name;
					}
					break;
				default: // strings
					if ($column->size > 0) {
						$lengths[$column->size][] = $column->name;
					} else {
						$types['string'][] = $column->name;
					}
			}
		}

		$rules = [];
		foreach ($types as $type => $columns) {
			$rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
		}
		foreach ($lengths as $length => $columns) {
			$rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
		}

		return $rules;
	}

	public function generateRelationUses($relations) {
		$uses = [];
		foreach ($relations as $relation) {
			if (isset($this->modelMap[$relation[1]])) {
				$uses[] = 'use '. $this->modelMap[$relation[1]] .';';
			}
		}
		return implode("\n", array_unique($uses));
	}

	/**
	 * @return array the generated relation declarations
	 */
	protected function generateRelations()
	{
		if (!$this->generateRelations) {
			return [];
		}

		$db = $this->getDbConnection();

		if (($pos = strpos($this->tableName, '.')) !== false) {
			$schemaName = substr($this->tableName, 0, $pos);
		} else {
			$schemaName = '';
		}

		$relations = [];
		foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
			$tableName = $table->name;
			$className = $this->generateClassName($tableName);
			foreach ($table->foreignKeys as $refs) {
				$refTable = $refs[0];
				unset($refs[0]);
				$fks = array_keys($refs);
				$refClassName = $this->generateClassName($refTable);

				// Add relation for this table
				$link = $this->generateRelationLink(array_flip($refs));
				$relationName = $this->generateRelationName($relations, $className, $table, $fks[0], false);
				if ($relationName === 'Id0') {
					$relationName = 'Registry';
				}
				$relations[$className][$relationName] = [
					"return \$this->hasOne($refClassName::className(), $link);",
					$refClassName,
					false,
				];

				// Add relation for the referenced table
				$hasMany = false;
				foreach ($fks as $key) {
					if (!in_array($key, $table->primaryKey, true)) {
						$hasMany = true;
						break;
					}
				}
				$link = $this->generateRelationLink($refs);
				$relationName = $this->generateRelationName($relations, $refClassName, $refTable, $className, $hasMany);
				$relations[$refClassName][$relationName] = [
					"return \$this->" . ($hasMany ? 'hasMany' : 'hasOne') . "($className::className(), $link);",
					$className,
					$hasMany,
				];
			}

			if (($fks = $this->checkPivotTable($table)) === false) {
				continue;
			}
			$table0 = $fks[$table->primaryKey[0]][0];
			$table1 = $fks[$table->primaryKey[1]][0];
			$className0 = $this->generateClassName($table0);
			$className1 = $this->generateClassName($table1);

			$link = $this->generateRelationLink([$fks[$table->primaryKey[1]][1] => $table->primaryKey[1]]);
			$viaLink = $this->generateRelationLink([$table->primaryKey[0] => $fks[$table->primaryKey[0]][1]]);
			$relationName = $this->generateRelationName($relations, $className0, $db->getTableSchema($table0), $table->primaryKey[1], true);
			$relations[$className0][$relationName] = [
				"return \$this->hasMany($className1::className(), $link)->viaTable('{$table->name}', $viaLink);",
				$className0,
				true,
			];

			$link = $this->generateRelationLink([$fks[$table->primaryKey[0]][1] => $table->primaryKey[0]]);
			$viaLink = $this->generateRelationLink([$table->primaryKey[1] => $fks[$table->primaryKey[1]][1]]);
			$relationName = $this->generateRelationName($relations, $className1, $db->getTableSchema($table1), $table->primaryKey[0], true);
			$relations[$className1][$relationName] = [
				"return \$this->hasMany($className0::className(), $link)->viaTable('{$table->name}', $viaLink);",
				$className1,
				true,
			];
		}
		return $relations;
	}

	/**
	 * Generates the link parameter to be used in generating the relation declaration.
	 * @param array $refs reference constraint
	 * @return string the generated link parameter.
	 */
	protected function generateRelationLink($refs)
	{
		$pairs = [];
		foreach ($refs as $a => $b) {
			$pairs[] = "'$a' => '$b'";
		}
		return '[' . implode(', ', $pairs) . ']';
	}

	/**
	 * Checks if the given table is a pivot table.
	 * For simplicity, this method only deals with the case where the pivot contains two PK columns,
	 * each referencing a column in a different table.
	 * @param \yii\db\TableSchema the table being checked
	 * @return array|boolean the relevant foreign key constraint information if the table is a pivot table,
	 * or false if the table is not a pivot table.
	 */
	protected function checkPivotTable($table)
	{
		$pk = $table->primaryKey;
		if (count($pk) !== 2) {
			return false;
		}
		$fks = [];
		foreach ($table->foreignKeys as $refs) {
			if (count($refs) === 2) {
				if (isset($refs[$pk[0]])) {
					$fks[$pk[0]] = [$refs[0], $refs[$pk[0]]];
				} elseif (isset($refs[$pk[1]])) {
					$fks[$pk[1]] = [$refs[0], $refs[$pk[1]]];
				}
			}
		}
		if (count($fks) === 2 && $fks[$pk[0]][0] !== $fks[$pk[1]][0]) {
			return $fks;
		} else {
			return false;
		}
	}

	/**
	 * Generate a relation name for the specified table and a base name.
	 * @param array $relations the relations being generated currently.
	 * @param string $className the class name that will contain the relation declarations
	 * @param \yii\db\TableSchema $table the table schema
	 * @param string $key a base name that the relation name may be generated from
	 * @param boolean $multiple whether this is a has-many relation
	 * @return string the relation name
	 */
	protected function generateRelationName($relations, $className, $table, $key, $multiple)
	{
		if (strcasecmp(substr($key, -2), 'id') === 0 && strcasecmp($key, 'id')) {
			$key = rtrim(substr($key, 0, -2), '_');
		}
		if ($multiple) {
			$key = Inflector::pluralize($key);
		}
		$name = $rawName = Inflector::id2camel($key, '_');
		$i = 0;
		while (isset($table->columns[lcfirst($name)])) {
			$name = $rawName . ($i++);
		}
		while (isset($relations[$className][lcfirst($name)])) {
			$name = $rawName . ($i++);
		}

		return $name;
	}

	/**
	 * Validates the [[db]] attribute.
	 */
	public function validateDb()
	{
		if (Yii::$app->hasComponent($this->db) === false) {
			$this->addError('db', 'There is no application component named "db".');
		} elseif (!Yii::$app->getComponent($this->db) instanceof Connection) {
			$this->addError('db', 'The "db" application component must be a DB connection instance.');
		}
	}

	/**
	 * Validates the [[ns]] attribute.
	 */
	public function validateNamespace()
	{
		$this->ns = ltrim($this->ns, '\\');
		$path = Yii::getAlias('@' . str_replace('\\', '/', $this->ns), false);
		if ($path === false) {
			$this->addError('ns', 'Namespace must be associated with an existing directory.');
		}
	}

	/**
	 * Validates the [[modelClass]] attribute.
	 */
	public function validateModelClass()
	{
		if ($this->isReservedKeyword($this->modelClass)) {
			$this->addError('modelClass', 'Class name cannot be a reserved PHP keyword.');
		}
		if (strpos($this->tableName, '*') === false && $this->modelClass == '') {
			$this->addError('modelClass', 'Model Class cannot be blank.');
		}
	}

	/**
	 * Validates the [[tableName]] attribute.
	 */
	public function validateTableName()
	{
		if (($pos = strpos($this->tableName, '*')) !== false && strpos($this->tableName, '*', $pos + 1) !== false) {
			$this->addError('tableName', 'At most one asterisk is allowed.');
			return;
		}
		$tables = $this->getTableNames();
		if (empty($tables)) {
			$this->addError('tableName', "Table '{$this->tableName}' does not exist.");
		} else {
			foreach ($tables as $table) {
				$class = $this->generateClassName($table);
				if ($this->isReservedKeyword($class)) {
					$this->addError('tableName', "Table '$table' will generate a class which is a reserved PHP keyword.");
					break;
				}
			}
		}
	}

	private $_tableNames;
	private $_classNames;

	/**
	 * @return array the table names that match the pattern specified by [[tableName]].
	 */
	protected function getTableNames()
	{
		if ($this->_tableNames !== null) {
			return $this->_tableNames;
		}
		$db = $this->getDbConnection();
		if ($db === null) {
			return [];
		}
		$tableNames = [];
		if (strpos($this->tableName, '*') !== false) {
			if (($pos = strrpos($this->tableName, '.')) !== false) {
				$schema = substr($this->tableName, 0, $pos);
				$pattern = '/^' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '$/';
			} else {
				$schema = '';
				$pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';
			}

			foreach ($db->schema->getTableNames($schema) as $table) {
				if (preg_match($pattern, $table)) {
					$tableNames[] = $schema === '' ? $table : ($schema . '.' . $table);
				}
			}
		} elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
			$tableNames[] = $this->tableName;
			$this->_classNames[$this->tableName] = $this->modelClass;
		}
		return $this->_tableNames = $tableNames;
	}

	/**
	 * Generates a class name from the specified table name.
	 * @param string $tableName the table name (which may contain schema prefix)
	 * @return string the generated class name
	 */
	protected function generateClassName($tableName)
	{
		if (isset($this->_classNames[$tableName])) {
			return $this->_classNames[$tableName];
		}

		if (($pos = strrpos($tableName, '.')) !== false) {
			$tableName = substr($tableName, $pos + 1);
		}

		$db = $this->getDbConnection();
		$patterns = [];
		if (strpos($this->tableName, '*') !== false) {
			$pattern = $this->tableName;
			if (($pos = strrpos($pattern, '.')) !== false) {
				$pattern = substr($pattern, $pos + 1);
			}
			$patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
		}
		if (!empty($db->tablePrefix)) {
			$patterns[] = "/^{$db->tablePrefix}(.*?)$/";
			$patterns[] = "/^(.*?){$db->tablePrefix}$/";
		} else {
			$patterns[] = "/^tbl_(.*?)$/";
		}

		$className = $tableName;
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $tableName, $matches)) {
				$className = $matches[1];
				break;
			}
		}
		return $this->_classNames[$tableName] = Inflector::id2camel($className, '_');
	}

	public function getSearchModels() {
		return ['@cascade/models' => 'cascade\models'];
	}

	public function getModelMap() {
		$m = [];
		$search = [];
		foreach ($this->searchModels as $path => $namespace) {
			$files = FileHelper::findFiles(Yii::getAlias($path), ['only' => ['.php']]);
			foreach ($files as $file) {
				$baseName = strstr(basename($file), '.php', true);
				$className = $namespace .'\\'. $baseName;
				if (class_exists($className)) {
					$reflector = new \ReflectionClass($className);
					if ($reflector->isSubclassOf('yii\base\Model')) {
						$m[$baseName] = $className;
					}
				}
			}
		}
		return $m;
	}
	/**
	 * @return Connection the DB connection as specified by [[db]].
	 */
	protected function getDbConnection()
	{
		return Yii::$app->{$this->db};
	}

}
