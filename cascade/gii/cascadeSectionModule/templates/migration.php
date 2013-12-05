<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 *
 * @var string $className the new migration class name
 */
echo "<?php\n";
?>
namespace <?= $migrationsNamespace; ?>;

class <?=$migrationClassName; ?> extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('<?= $tableName; ?>');
		
		$this->createTable('<?= $tableName; ?>', [
			<?= $createTableSyntax; ?>

		]);

		<?= $createTableIndices; ?>


		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('<?= $tableName; ?>');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
