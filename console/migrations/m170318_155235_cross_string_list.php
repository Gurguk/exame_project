<?php

use yii\db\Migration;

class m170318_155235_cross_string_list extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cross_string_list}}', [
            'id' => $this->primaryKey(),
            'id_category' => $this->integer()->notNull(),
            'id_section' => $this->integer()->notNull(),
            'length' => $this->integer()->notNull(),
            'value' => $this->string(20)->notNull(),
            'question' => $this->string()->notNull(),

        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cross_string_list}}');
    }
}
