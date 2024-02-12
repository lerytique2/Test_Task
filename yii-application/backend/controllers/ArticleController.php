<?php

namespace backend\controllers;

use Throwable;
use Yii;
use common\models\Article;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;

class ArticleController extends ActiveController
{
    public $modelClass = 'common\models\Article';

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // Аутентификация через Bearer Token
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view'], // Публичный доступ к просмотру статей
        ];

        // Контроль доступа
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete'],
                    'roles' => ['@'], // Доступ только для аутентифицированных пользователей
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'roles' => ['?', '@'], // Доступ для всех
                ],
            ],
        ];

        // Ограничения на HTTP методы
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    public function actions(): array
    {
        $actions = parent::actions();

        unset($actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    // Переопределение actionIndex для добавления фильтрации
    public function actionIndex(): ActiveDataProvider
    {
        $params = Yii::$app->request->queryParams;
        $query = Article::find();

        // Пример фильтрации по статусу, названию, автору и дате публикации
        $query->andFilterWhere(['status' => $params['status'] ?? Article::STATUS_PUBLISHED]);
        $query->andFilterWhere(['like', 'title', $params['title'] ?? '']);
        $query->andFilterWhere(['author_id' => $params['author_id'] ?? '']);
        $query->andFilterWhere(['>=', 'published_at', $params['published_from'] ?? '']);
        $query->andFilterWhere(['<=', 'published_at', $params['published_to'] ?? '']);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['published_at' => SORT_DESC]],
        ]);
    }

    public function actionCreate(): Article
    {
        if (!Yii::$app->user->can('createArticle')) {
            throw new ForbiddenHttpException('You are not allowed to create articles.');
        }

        $model = new Article();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
            return $model;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        return $model;
    }

    public function actionUpdate($id): ?Article
    {
        if (!Yii::$app->user->can('updateArticle')) {
            throw new ForbiddenHttpException('You are not allowed to update this article.');
        }

        $model = $this->findModel($id);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            return $model;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        return $model;
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id): void
    {
        if (!Yii::$app->user->can('deleteArticle')) {
            throw new ForbiddenHttpException('You are not allowed to delete this article.');
        }

        $model = $this->findModel($id);
        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel($id): ?Article
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException("The requested article does not exist.");
    }
}
