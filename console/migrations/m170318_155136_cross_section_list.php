<?php

use yii\db\Migration;

class m170318_155136_cross_section_list extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cross_section_list}}', [
            'id' => $this->primaryKey(),
            'id_category' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()->unique(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cross_section_list}}');
    }
}
