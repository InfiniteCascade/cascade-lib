<?php
namespace cascade\modules\TypeEmailAddress\migrations;

class m131101_212903_initial_object_email_address extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_email_address');
		
		$this->createTable('object_email_address', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'email_address' => 'string(255) NOT NULL',
			'no_mailings' => 'boolean NOT NULL DEFAULT 0',
			'created' => 'datetime DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL'
		]);

		$this->addForeignKey('emailAddressRegistry', 'object_email_address', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_email_address');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
