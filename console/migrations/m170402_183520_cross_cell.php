<?php

use yii\db\Migration;

class m170402_183520_cross_cell extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cross_cell}}', [
            'id' => $this->primaryKey(),
            'grid_id' => $this->integer(11)->notNull(),
            'x' => $this->integer(3)->notNull(),
            'y' => $this->integer(3)->notNull(),
            'crossed' => $this->integer(1)->defaultValue(0),
            'letter' => $this->string(1)->notNull(),
            'number' => $this->integer(2)->notNull(),
            'word_id' => $this->integer(11)->notNull(),
            'can_cross_h' => $this->integer(1)->notNull()->defaultValue(1),
            'can_cross_v' => $this->integer(1)->notNull()->defaultValue(1),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cross_cell}}');
    }
}
