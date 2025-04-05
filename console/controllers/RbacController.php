<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Контроллер для инициализации RBAC разрешений
 */
class RbacController extends Controller
{
    /**
     * Инициализация RBAC разрешений
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        
        // Очищаем существующие разрешения и роли
        $auth->removeAll();
        
        // Создаем разрешения для работы с клубами
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
        
        // Создаем разрешения для работы с клиентами
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
        
        // Назначаем все разрешения администратору
        $auth->addChild($admin, $createClub);
        $auth->addChild($admin, $updateClub);
        $auth->addChild($admin, $deleteClub);
        $auth->addChild($admin, $restoreClub);
        $auth->addChild($admin, $createClient);
        $auth->addChild($admin, $updateClient);
        $auth->addChild($admin, $deleteClient);
        $auth->addChild($admin, $restoreClient);
        $auth->addChild($admin, $exportClients);
        
        // Создаем роль менеджера
        $manager = $auth->createRole('manager');
        $manager->description = 'Менеджер';
        $auth->add($manager);
        
        // Назначаем разрешения менеджеру
        $auth->addChild($manager, $createClient);
        $auth->addChild($manager, $updateClient);
        $auth->addChild($manager, $deleteClient);
        $auth->addChild($manager, $restoreClient);
        $auth->addChild($manager, $exportClients);
        
        // Назначаем роли администратора существующему пользователю с ID 1
        $auth->assign($admin, 1);
        
        echo "RBAC разрешения успешно инициализированы.\n";
    }
} 