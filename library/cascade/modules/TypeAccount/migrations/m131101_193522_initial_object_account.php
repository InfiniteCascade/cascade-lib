<?php
namespace cascade\modules\TypeAccount\migrations;

class m131101_193522_initial_object_account extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_account');
		
		$this->createTable('object_account', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'name' => 'string(255) NOT NULL',
			'alt_name' => 'string(255) DEFAULT NULL',
			'created' => 'datetime DEFAULT NULL',
			'created_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL',
			'modified_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
			'deleted' => 'datetime DEFAULT NULL',
			'deleted_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL'
		]);

		$this->createIndex('accountCreatedUser', 'object_account', 'created_user_id', false);
		$this->createIndex('accountModifiedUser', 'object_account', 'modified_user_id', false);
		$this->createIndex('accountDeletedUser', 'object_account', 'deleted_user_id', false);
		$this->addForeignKey('accountCreatedUser', 'object_account', 'created_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('accountDeletedUser', 'object_account', 'deleted_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('accountModfiedUser', 'object_account', 'modified_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('accountRegistry', 'object_account', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_account');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
