<?php
namespace cascade\modules\TypeTime\migrations;

class m131213_230842_initial_object_time extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_time');
		
		$this->createTable('object_time', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'description' => 'text DEFAULT NULL',
			'hours' => 'decimal(10,2) NOT NULL DEFAULT \'0.00\'',
			'log_date' => 'date DEFAULT NULL',
			'created' => 'datetime DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL'
		]);

		$this->addForeignKey('objectTimeRegistry', 'object_time', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_time');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
