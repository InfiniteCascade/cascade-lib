<?php
namespace cascade\migrations;

class m131031_173117_initial_core_cascade extends \infinite\db\Migration
{
    public function up()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();
        // data_interface
        $this->dropExistingTable('data_interface');
        $this->createTable('data_interface', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'name' => 'string DEFAULT NULL',
            'system_id' => 'string NOT NULL',
            'last_sync' => 'datetime DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        // $this->addPrimaryKey('dataInterfacePk', 'data_interface', 'id');
        $this->addForeignKey('dataInterfaceRegistry', 'data_interface', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

        // data_interface_log
        $this->dropExistingTable('data_interface_log');
        $this->createTable('data_interface_log', [
            'id' => 'bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'data_interface_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'status' => 'string DEFAULT \'running\'',
            'message' => 'longblob DEFAULT NULL',
            'peak_memory' => 'integer unsigned DEFAULT NULL',
            'started' => 'datetime DEFAULT NULL',
            'ended' => 'datetime DEFAULT NULL',
        ]);
        // $this->addPrimaryKey('primary0', 'data_interface_log', 'id');
        $this->createIndex('dataInterfaceLogRegistry', 'data_interface_log', 'data_interface_id', false);
        $this->addForeignKey('dataInterfaceLogRegistry', 'data_interface_log', 'data_interface_id', 'data_interface', 'id', 'CASCADE', 'CASCADE');

        // key_translation
        $this->dropExistingTable('key_translation');
        $this->createTable('key_translation', [
            'id' => 'bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'data_interface_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'registry_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT \'\'',
            'key' => 'string NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        // $this->addPrimaryKey('keyTranslationPk', 'key_translation', 'id');
        $this->createIndex('keyTranslationInterface', 'key_translation', 'data_interface_id', false);
        $this->createIndex('keyTranslationInterfaceKey', 'key_translation', 'data_interface_id,key', false);
        $this->createIndex('keyTranslationRegistry', 'key_translation', 'registry_id', false);
        $this->addForeignKey('keyTranslationInterface', 'key_translation', 'data_interface_id', 'data_interface', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('keyTranslationRegistryFk', 'key_translation', 'registry_id', 'registry', 'id', 'CASCADE', 'CASCADE');

        // object_familiarity
        $this->dropExistingTable('object_familiarity');
        $this->createTable('object_familiarity', [
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'model' => 'string DEFAULT NULL',
            'watching' => 'bool NOT NULL DEFAULT 0',
            'created' => 'bool NOT NULL DEFAULT 0',
            'modified' => 'integer unsigned NOT NULL DEFAULT 0',
            'accessed' => 'integer unsigned NOT NULL DEFAULT 0',
            'familiarity' => 'integer unsigned NOT NULL',
            'session' => 'char(32) DEFAULT NULL',
            'last_modified' => 'datetime DEFAULT NULL',
            'last_accessed' => 'datetime DEFAULT NULL',
            'first_accessed' => 'datetime DEFAULT NULL'
        ]);
        $this->addPrimaryKey('objectFamiliarityPk', 'object_familiarity', 'object_id,user_id');
        $this->createIndex('objectFamiliarityUser', 'object_familiarity', 'user_id', false);
        $this->createIndex('objectFamiliarityObject', 'object_familiarity', 'object_id,user_id', false);
        $this->createIndex('objectFamiliarityUserModel', 'object_familiarity', 'user_id,model', false);
        $this->addForeignKey('objectFamiliarityObjectRegistry', 'object_familiarity', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('objectFamiliarityUser', 'object_familiarity', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');

        // meta_key
        $this->dropExistingTable('meta_key');
        $this->createTable('meta_key', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'name' => 'string DEFAULT NULL',
            'value_type' => 'string NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        // $this->addPrimaryKey('metaKeyPk', 'meta_key', 'id');
        $this->addForeignKey('metaKeyRegistry', 'meta_key', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

        // meta
        $this->dropExistingTable('meta');
        $this->createTable('meta', [
            'id' => 'bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'registry_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'meta_key_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'value_text' => 'longtext DEFAULT NULL',
            'value_int' => 'integer DEFAULT NULL',
            'value_float' => 'float DEFAULT NULL',
            'value_datetime' => 'datetime DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);

        // $this->addPrimaryKey('metaPk', 'meta', 'id');
        $this->createIndex('metaRegistry', 'meta', 'registry_id', false);
        $this->createIndex('metaRegistryKey', 'meta', 'registry_id,meta_key_id', false);
        $this->createIndex('metaMetaKey', 'meta', 'meta_key_id', false);
        $this->addForeignKey('metaRegistryFk', 'meta', 'registry_id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('metaMetaKey', 'meta', 'meta_key_id', 'meta_key', 'id', 'CASCADE', 'CASCADE');

        // object_type
        $this->dropExistingTable('object_type');
        $this->createTable('object_type', [
            'name' => 'string NOT NULL PRIMARY KEY',
            'system_version' => 'float DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        // $this->addPrimaryKey('primary0', 'object_type', 'name');

        // relation_taxonomy
        $this->dropExistingTable('relation_taxonomy');
        $this->createTable('relation_taxonomy', [
            'id' => 'bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'relation_id' => 'bigint unsigned NOT NULL',
            'taxonomy_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL'
        ]);
        // $this->addPrimaryKey('primary0', 'relation_taxonomy', 'id');
        $this->createIndex('rtRelation', 'relation_taxonomy', 'relation_id', false);
        $this->createIndex('rtTaxonomy', 'relation_taxonomy', 'taxonomy_id', false);
        $this->addForeignKey('rtRelationFk', 'relation_taxonomy', 'relation_id', 'relation', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('rtTaxonomyFk', 'relation_taxonomy', 'taxonomy_id', 'taxonomy', 'id', 'CASCADE', 'CASCADE');
        
        // taxonomy
        $this->dropExistingTable('taxonomy');
        $this->createTable('taxonomy', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'taxonomy_type_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'name' => 'string NOT NULL',
            'system_id' => 'string DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        // $this->addPrimaryKey('taxonomyPk', 'taxonomy', 'id');
        $this->createIndex('taxonomyType', 'taxonomy', 'taxonomy_type_id', false);
        $this->addForeignKey('taxonomyRegistry', 'taxonomy', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('taxonomyType', 'taxonomy', 'taxonomy_type_id', 'taxonomy_type', 'id', 'CASCADE', 'CASCADE');

        // taxonomy_type
        $this->dropExistingTable('taxonomy_type');
        $this->createTable('taxonomy_type', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'name' => 'string NOT NULL',
            'system_id' => 'string DEFAULT NULL',
            'system_version' => 'float unsigned DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        // $this->addPrimaryKey('taxonomyTypePk', 'taxonomy_type', 'id');
        $this->addForeignKey('taxonomyTypeRegistry', 'taxonomy_type', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }

    public function down()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        $this->dropExistingTable('data_interface');
        $this->dropExistingTable('data_interface_log');
        $this->dropExistingTable('key_translation');
        $this->dropExistingTable('object_familiarity');
        $this->dropExistingTable('meta_key');
        $this->dropExistingTable('meta');
        $this->dropExistingTable('object_model');
        $this->dropExistingTable('relation_taxonomy');
        $this->dropExistingTable('taxonomy');
        $this->dropExistingTable('taxonomy_type');

        $this->db->createCommand()->checkIntegrity(true)->execute();
        return true;
    }
}