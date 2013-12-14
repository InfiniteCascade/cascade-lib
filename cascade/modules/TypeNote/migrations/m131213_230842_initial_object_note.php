<?php
namespace cascade\modules\TypeNote\migrations;

class m131213_230842_initial_object_note extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_note');
		
		$this->createTable('object_note', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'title' => 'string(255) DEFAULT NULL',
			'note' => 'text DEFAULT NULL',
			'created' => 'datetime DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL'
		]);

		$this->addForeignKey('objectNoteRegistry', 'object_note', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_note');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
