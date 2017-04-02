<?php

use yii\db\Migration;

class m170402_184648_cross_word extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cross_word}}', [
            'id' => $this->primaryKey(),
            'word_id' => $this->integer(11)->notNull(),
            'axis' => $this->integer(1)->notNull(),
            'fully_crossed' => $this->integer(1)->notNull(),
            'number' => $this->integer(2)->notNull(),
            'x' => $this->integer(3)->notNull(),
            'y' => $this->integer(3)->notNull(),
            'length' => $this->integer(2)->notNull(),
            'grid_id' => $this->integer(11)->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cross_word}}');
    }
}
