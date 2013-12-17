<?php
namespace cascade\modules\TypeIndividual\migrations;

class m131101_202825_initial_object_individual extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_individual');
		
		$this->createTable('object_individual', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
			'prefix' => 'string(255) DEFAULT NULL',
			'suffix' => 'string(255) DEFAULT NULL',
			'first_name' => 'string(255) NOT NULL',
			'middle_name' => 'string(255) DEFAULT NULL',
			'last_name' => 'string(255) DEFAULT \'\'',
			'title' => 'string(255) DEFAULT NULL',
			'department' => 'string(255) DEFAULT NULL',
			'birthday' => 'date DEFAULT NULL',
			'created' => 'datetime DEFAULT NULL',
			'created_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL',
			'modified_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
			'deleted' => 'datetime DEFAULT NULL',
			'deleted_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL'
		]);

		$this->createIndex('individualUser', 'object_individual', 'user_id', false);
		$this->createIndex('individualCreatedUser', 'object_individual', 'created_user_id', false);
		$this->createIndex('individualModifiedUser', 'object_individual', 'modified_user_id', false);
		$this->createIndex('individualDeletedUser', 'object_individual', 'deleted_user_id', false);
		$this->createIndex('individualRegistry', 'object_individual', 'id', false);
		$this->addForeignKey('individualCreatedUser', 'object_individual', 'created_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('individualDeletedUser', 'object_individual', 'deleted_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('individualModifiedUser', 'object_individual', 'modified_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('individualRegistry', 'object_individual', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('indvidualUser', 'object_individual', 'user_id', 'user', 'id', 'SET NULL', 'SET NULL');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_individual');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
