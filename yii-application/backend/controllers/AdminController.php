<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\Article;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

class AdminController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                         'matchCallback' => function ($rule, $action) {
                             return Yii::$app->user->identity->isAdmin; }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-user' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список всех пользователей
     */
    public function actionIndex(): string
    {
        $users = User::find()->all();
        return $this->render('index', [
            'users' => $users,
        ]);
    }

    /**
     * Изменение роли пользователя
     */
    /**
     * Изменение роли пользователя
     */
    public function actionChangeRole($userId, $newRoleName): Response
    {
        $user = User::findOne($userId);
        if (!$user) {
            Yii::$app->session->setFlash('error', "Пользователь не найден.");
            return $this->redirect(['index']);
        }

        $auth = Yii::$app->authManager;
        $newRole = $auth->getRole($newRoleName);
        if (!$newRole) {
            Yii::$app->session->setFlash('error', "Роль '$newRoleName' не существует.");
            return $this->redirect(['index']);
        }

        $auth->revokeAll($userId); // Удалить все существующие роли пользователя
        $auth->assign($newRole, $userId); // Назначить новую роль пользователю

        Yii::$app->session->setFlash('success', "Роль пользователя успешно изменена на '$newRoleName'.");
        return $this->redirect(['index']);
    }

    /**
     * Просмотр и модерация статей
     */
    public function actionArticles(): string
    {
        $articles = Article::find()->all();
        return $this->render('articles', [
            'articles' => $articles,
        ]);
    }

    /**
     * Одобрение статьи
     */
    public function actionApproveArticle($articleId): Response
    {

        $article = Article::findOne($articleId);
        if (!$article) {
            Yii::$app->session->setFlash('error', "Статья не найдена.");
            return $this->redirect(['articles']);
        }

        $article->status = Article::STATUS_PUBLISHED;
        if ($article->save()) {
            Yii::$app->session->setFlash('success', "Статья одобрена.");
        } else {
            Yii::$app->session->setFlash('error', "Ошибка при сохранении статьи.");
        }

        return $this->redirect(['articles']);
    }
}
