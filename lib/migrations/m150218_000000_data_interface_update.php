<?php
namespace cascade\migrations;

class m150218_000000_data_interface_update extends \canis\db\Migration
{
    public function up()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        // audit addition
        $this->addColumn('data_interface_log', 'last_update', 'datetime DEFAULT NULL AFTER ended');
        $this->addColumn('data_interface_log', 'created', 'datetime DEFAULT NULL AFTER last_update');
        $this->addColumn('data_interface_log', 'modified', 'datetime DEFAULT NULL AFTER created');
        $this->dropColumn('data_interface_log', 'status');
        $this->addColumn('data_interface_log', 'status', 'ENUM(\'queued\',\'running\',\'interrupted\',\'error\',\'success\')  DEFAULT \'queued\' AFTER data_interface_id');
        // ALTER TABLE `data_interface_log` CHANGE `status` `status` ENUM('queued','running','interrupted','error','completed')  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT 'queued';

        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }

    // public function down()
    // {
    //     $this->db->createCommand()->checkIntegrity(false)->execute();


    //     $this->db->createCommand()->checkIntegrity(true)->execute();

    //     return true;
    // }
}
