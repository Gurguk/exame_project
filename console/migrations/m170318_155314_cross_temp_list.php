<?php

use yii\db\Migration;

class m170318_155314_cross_temp_list extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cross_temp_list}}', [
            'groupid' => $this->integer()->notNull(),
            'wordid' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cross_temp_list}}');
    }
}
