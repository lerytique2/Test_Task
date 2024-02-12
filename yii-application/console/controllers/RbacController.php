<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit(): void
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); // Очистка старых данных RBAC

        // Создание ролей
        $user = $auth->createRole('user');
        $moderator = $auth->createRole('moderator');
        $admin = $auth->createRole('admin');

        // Добавление ролей в систему
        $auth->add($user);
        $auth->add($moderator);
        $auth->add($admin);

        // Создание разрешений
        $createPost = $auth->createPermission('createPost');
        $createPost->description = 'Create a post';
        $auth->add($createPost);

        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users';
        $auth->add($manageUsers);

        $moderateArticles = $auth->createPermission('moderateArticles');
        $moderateArticles->description = 'Moderate articles';
        $auth->add($moderateArticles);

        // Назначение разрешений ролям
        $auth->addChild($user, $createPost);

        $auth->addChild($moderator, $createPost);
        $auth->addChild($moderator, $moderateArticles);

        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $moderator); // Админ наследует разрешения модератора

        // Назначение роли 'admin' пользователю с ID 1
        $auth->assign($admin, 1);
    }
}


