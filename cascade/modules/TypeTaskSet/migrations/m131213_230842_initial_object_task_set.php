<?php
namespace cascade\modules\TypeTaskSet\migrations;

class m131213_230842_initial_object_task_set extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_task_set');
		
		$this->createTable('object_task_set', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'name' => 'string(255) NOT NULL',
			'created' => 'datetime DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL'
		]);

		$this->addForeignKey('objectTaskSetRegistry', 'object_task_set', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_task_set');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
