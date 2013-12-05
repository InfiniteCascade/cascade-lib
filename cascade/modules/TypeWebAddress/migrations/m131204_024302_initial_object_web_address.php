<?php
namespace cascade\modules\TypeWebAddress\migrations;

class m131204_024302_initial_object_web_address extends \infinite\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();

		$this->dropExistingTable('object_web_address');
		
		$this->createTable('object_web_address', [
			'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
			'title' => 'string(255) DEFAULT NULL',
			'url' => 'string(500) NOT NULL',
			'created' => 'datetime DEFAULT NULL',
			'modified' => 'datetime DEFAULT NULL'
		]);

		$this->addForeignKey('objectUrlRegistry', 'object_web_address', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

		$this->db->createCommand()->checkIntegrity(true)->execute();

		return true;
	}



	public function down()
	{
		$this->db->createCommand()->checkIntegrity(false)->execute();
		$this->dropExistingTable('object_web_address');
		$this->db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}
}
