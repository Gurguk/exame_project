<?php

use yii\db\Migration;

class m170402_184102_cross_grid extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cross_grid}}', [
            'id' => $this->primaryKey(),
            'rows' => $this->integer(2)->notNull(),
            'cols' => $this->integer(2)->notNull(),
            'totwords' => $this->integer(3)->notNull(),
            'crossword' => $this->integer(11)->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cross_grid}}');
    }
}
