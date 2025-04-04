<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%client}}`.
 */
class m250403_102759_create_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%client}}', [
            'id' => $this->primaryKey(),
            'club_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'surname' => $this->string()->notNull(),
            'phone' => $this->string(20)->notNull(),
            'email' => $this->string()->notNull(),
            'birth_date' => $this->date(),
            'gender' => $this->string(1),
            'address' => $this->string(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->timestamp()->null(),
            'deleted_by' => $this->integer(),
        ]);

        // Создаем индексы для оптимизации поиска
        $this->createIndex('idx-client-name', '{{%client}}', 'name');
        $this->createIndex('idx-client-surname', '{{%client}}', 'surname');
        $this->createIndex('idx-client-email', '{{%client}}', 'email');
        $this->createIndex('idx-client-phone', '{{%client}}', 'phone');
        $this->createIndex('idx-client-club', '{{%client}}', 'club_id');

        // Добавляем внешний ключ
        $this->addForeignKey(
            'fk-client-club_id',
            '{{%client}}',
            'club_id',
            '{{%club}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем внешний ключ
        $this->dropForeignKey('fk-client-club_id', '{{%client}}');

        // Удаляем индексы
        $this->dropIndex('idx-client-name', '{{%client}}');
        $this->dropIndex('idx-client-surname', '{{%client}}');
        $this->dropIndex('idx-client-email', '{{%client}}');
        $this->dropIndex('idx-client-phone', '{{%client}}');
        $this->dropIndex('idx-client-club', '{{%client}}');
        
        // Удаляем таблицу
        $this->dropTable('{{%client}}');
    }
}
