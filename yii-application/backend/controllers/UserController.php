<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\SignupForm;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // Настройка аутентификации через Bearer Token
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['create', 'login'], // Исключения для публичных действий
        ];

        // Настройка контроля доступа
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['create', 'login'],
                    'roles' => ['?'], // Гостевой доступ
                ],
                [
                    'allow' => true,
                    'actions' => ['view', 'update', 'delete'],
                    'roles' => ['@'], // Доступ для аутентифицированных пользователей
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions(): array
    {
        $actions = parent::actions();

        // Отключение стандартных действий, которые будут переопределены
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['view']);

        return $actions;
    }

    // Регистрация нового пользователя
    public function actionCreate(): string|SignupForm
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $user = $model->signup()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            // Назначение роли user новому пользователю
            $auth = Yii::$app->authManager;
            $userRole = $auth->getRole('user');
            $auth->assign($userRole, $user->getId());

            return "User successfully registered";
        } elseif ($model->hasErrors()) {
            return $model;
        } else {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
    }

    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            $user = $model->getUser();
            $jwt = Yii::$app->jwt;
            $signer = $jwt->getSigner('HS256');
            $key = $jwt->getKey();
            $time = time();

            $token = $jwt->getBuilder()
                ->issuedBy('http://example.com') // Configures the issuer (iss claim)
                ->permittedFor('http://example.org') // Configures the audience (aud claim)
                ->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                ->issuedAt($time) // Configures the time that the token was issued (iat claim)
                ->canOnlyBeUsedAfter($time + 60) // Configures the time before which the token cannot be accepted (nbf claim)
                ->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
                ->withClaim('uid', $user->id) // Configures a new claim, called "uid"
                ->getToken($signer, $key); // Retrieves the generated token

            // Возвращаем токен в ответе
            return [
                'access_token' => (string) $token,
            ];
        } else {
            Yii::$app->response->statusCode = 401;
            return $model->getErrors();
        }
    }

    public function actionView($id): ?User
    {
        return $this->findModel($id);
    }

    public function actionDelete($id): string
    {
        if (!Yii::$app->user->can('deleteUser', ['user_id' => $id])) {
            throw new ForbiddenHttpException('You are not allowed to delete this user.');
        }

        $model = $this->findModel($id);
        if ($model->delete()) {
            Yii::$app->getResponse()->setStatusCode(204);
            return "User successfully deleted";
        } else {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
    }

    protected function findModel($id): ?User
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested user does not exist.');
    }
}
