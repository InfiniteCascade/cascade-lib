<?php
namespace cascade\modules\TypeFile\migrations;

class m131213_230842_initial_object_file extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_file');
		
		$this->createTable('object_file', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'name' => 'string(255) DEFAULT NULL',
			'file_name' => 'string(255) NOT NULL',
			'type' => 'string(100) NOT NULL',
			'size' => 'integer unsigned NOT NULL',
			'created' => 'datetime DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL'
		]);

		$this->addForeignKey('objectFileRegistry', 'object_file', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_file');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
