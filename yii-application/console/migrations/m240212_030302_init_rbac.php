<?php

use yii\db\Migration;
use yii\rbac\DbManager;

/**
* Class m240212_030302_init_rbac
*/
class m240212_030302_init_rbac extends Migration
{
/**
* {@inheritdoc}
*/
public function safeUp(): void
{
$auth = new DbManager;
$auth->init();

// Очистка старых данных
$auth->removeAll();

// Создание ролей
$user = $auth->createRole('user');
$auth->add($user);

$moderator = $auth->createRole('moderator');
$auth->add($moderator);

$admin = $auth->createRole('admin');
$auth->add($admin);

// Создание базовых разрешений
$createPost = $auth->createPermission('createPost');
$createPost->description = 'Create a post';
$auth->add($createPost);

$viewPost = $auth->createPermission('viewPost');
$viewPost->description = 'View a post';
$auth->add($viewPost);

// Назначение разрешений ролям
$auth->addChild($user, $viewPost);
$auth->addChild($moderator, $createPost);
$auth->addChild($admin, $user);
$auth->addChild($admin, $moderator);
}

/**
* {@inheritdoc}
*/
public function safeDown(): void
{
$auth = new DbManager;
$auth->init();

// Удаление разрешений и ролей
$auth->removeAllPermissions();
$auth->removeAllRoles();
}
}
