<?php

use yii\db\Migration;
use yii\rbac\Item;

/**
 * Class m250404_193848_rbac_init
 */
class m250404_193848_rbac_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        
        // Создаем разрешения для клубов
        $createClub = $auth->createPermission('createClub');
        $createClub->description = 'Создание клуба';
        $auth->add($createClub);
        
        $updateClub = $auth->createPermission('updateClub');
        $updateClub->description = 'Обновление клуба';
        $auth->add($updateClub);
        
        $deleteClub = $auth->createPermission('deleteClub');
        $deleteClub->description = 'Удаление клуба';
        $auth->add($deleteClub);
        
        $restoreClub = $auth->createPermission('restoreClub');
        $restoreClub->description = 'Восстановление клуба';
        $auth->add($restoreClub);
        
        // Создаем разрешения для клиентов
        $createClient = $auth->createPermission('createClient');
        $createClient->description = 'Создание клиента';
        $auth->add($createClient);
        
        $updateClient = $auth->createPermission('updateClient');
        $updateClient->description = 'Обновление клиента';
        $auth->add($updateClient);
        
        $deleteClient = $auth->createPermission('deleteClient');
        $deleteClient->description = 'Удаление клиента';
        $auth->add($deleteClient);
        
        $restoreClient = $auth->createPermission('restoreClient');
        $restoreClient->description = 'Восстановление клиента';
        $auth->add($restoreClient);
        
        $exportClients = $auth->createPermission('exportClients');
        $exportClients->description = 'Экспорт клиентов';
        $auth->add($exportClients);
        
        // Создаем роль администратора
        $admin = $auth->createRole('admin');
        $admin->description = 'Администратор';
        $auth->add($admin);
        
        // Назначаем все разрешения роли администратора
        $auth->addChild($admin, $createClub);
        $auth->addChild($admin, $updateClub);
        $auth->addChild($admin, $deleteClub);
        $auth->addChild($admin, $restoreClub);
        
        $auth->addChild($admin, $createClient);
        $auth->addChild($admin, $updateClient);
        $auth->addChild($admin, $deleteClient);
        $auth->addChild($admin, $restoreClient);
        $auth->addChild($admin, $exportClients);
        
        // Назначаем роль администратора пользователю с ID=1 (admin)
        $auth->assign($admin, 1);
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();
        
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250404_193848_rbac_init cannot be reverted.\n";

        return false;
    }
    */
}
