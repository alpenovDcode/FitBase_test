<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%club}}`.
 */
class m250403_102707_create_club_table extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%club}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'address' => $this->string()->notNull(),
            'phone' => $this->string(20)->notNull(),
            'email' => $this->string()->notNull(),
            'description' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->timestamp()->null(),
            'deleted_by' => $this->integer(),
        ]);

        // Создаем индекс для оптимизации поиска
        $this->createIndex('idx-club-name', '{{%club}}', 'name');
        $this->createIndex('idx-club-email', '{{%club}}', 'email');
        $this->createIndex('idx-club-phone', '{{%club}}', 'phone');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-club-name', '{{%club}}');
        $this->dropIndex('idx-club-email', '{{%club}}');
        $this->dropIndex('idx-club-phone', '{{%club}}');
        
        $this->dropTable('{{%club}}');
    }
}
