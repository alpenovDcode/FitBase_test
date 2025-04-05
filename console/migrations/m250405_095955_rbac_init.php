<?php

use yii\db\Migration;

class m250405_095955_rbac_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Создаем таблицу auth_rule
        $this->createTable('{{%auth_rule}}', [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
        ], $tableOptions);

        // Создаем таблицу auth_item
        $this->createTable('{{%auth_item}}', [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
            'FOREIGN KEY ([[rule_name]]) REFERENCES {{%auth_rule}} ([[name]])' .
                ' ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        $this->createIndex('idx-auth_item-type', '{{%auth_item}}', 'type');

        // Создаем таблицу auth_item_child
        $this->createTable('{{%auth_item_child}}', [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY ([[parent]], [[child]])',
            'FOREIGN KEY ([[parent]]) REFERENCES {{%auth_item}} ([[name]])'.
                ' ON DELETE CASCADE ON UPDATE CASCADE',
            'FOREIGN KEY ([[child]]) REFERENCES {{%auth_item}} ([[name]])'.
                ' ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);

        // Создаем таблицу auth_assignment
        $this->createTable('{{%auth_assignment}}', [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY ([[item_name]], [[user_id]])',
            'FOREIGN KEY ([[item_name]]) REFERENCES {{%auth_item}} ([[name]])' .
                ' ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);

        // Создаем базовые права (через SQL напрямую)
        $time = time();
        
        // Разрешения для клубов
        $this->insert('{{%auth_item}}', [
            'name' => 'createClub',
            'type' => 2,
            'description' => 'Создание клуба',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        $this->insert('{{%auth_item}}', [
            'name' => 'updateClub',
            'type' => 2,
            'description' => 'Редактирование клуба',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        $this->insert('{{%auth_item}}', [
            'name' => 'deleteClub',
            'type' => 2,
            'description' => 'Удаление клуба',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        // Разрешения для клиентов
        $this->insert('{{%auth_item}}', [
            'name' => 'createClient',
            'type' => 2,
            'description' => 'Создание клиента',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        $this->insert('{{%auth_item}}', [
            'name' => 'updateClient',
            'type' => 2,
            'description' => 'Редактирование клиента',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        $this->insert('{{%auth_item}}', [
            'name' => 'deleteClient',
            'type' => 2,
            'description' => 'Удаление клиента',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        // Роль администратора
        $this->insert('{{%auth_item}}', [
            'name' => 'admin',
            'type' => 1,
            'description' => 'Администратор',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        
        // Связи ролей и разрешений
        $this->insert('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => 'createClub',
        ]);
        $this->insert('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => 'updateClub',
        ]);
        $this->insert('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => 'deleteClub',
        ]);
        $this->insert('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => 'createClient',
        ]);
        $this->insert('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => 'updateClient',
        ]);
        $this->insert('{{%auth_item_child}}', [
            'parent' => 'admin',
            'child' => 'deleteClient',
        ]);
        
        // Назначаем роль администратора пользователю с ID 1
        $this->insert('{{%auth_assignment}}', [
            'item_name' => 'admin',
            'user_id' => '1',
            'created_at' => $time,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
        $this->dropTable('{{%auth_rule}}');
        
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250405_095955_rbac_init cannot be reverted.\n";

        return false;
    }
    */
}
