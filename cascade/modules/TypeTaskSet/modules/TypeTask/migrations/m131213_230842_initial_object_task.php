<?php
namespace cascade\modules\TypeTaskSet\modules\TypeTask\migrations;

class m131213_230842_initial_object_task extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_task');
		
		$this->createTable('object_task', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'object_task_set_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
			'description' => 'text NOT NULL',
			'start' => 'date DEFAULT NULL',
			'end' => 'date DEFAULT NULL',
			'completed' => 'smallint() unsigned NOT NULL DEFAULT 0',
			'created' => 'datetime DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL'
		]);

		$this->createIndex('objectTaskTaskSet', 'object_task', 'object_task_set_id', false);
		$this->addForeignKey('objectTaskRegistry', 'object_task', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('objectTaskSet', 'object_task', 'object_task_set_id', 'object_task_set', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_task');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
