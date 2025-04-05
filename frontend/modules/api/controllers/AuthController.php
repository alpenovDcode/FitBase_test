<?php

namespace frontend\modules\api\controllers;

use common\models\LoginForm;
use common\models\User;
use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use common\components\HttpBearerAuth;

/**
 * API контроллер для аутентификации
 */
class AuthController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Настройка формата ответа
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        
        // Настройка CORS
        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        
        // Добавляем HTTP Bearer Authentication, кроме логина и options
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login', 'options'],
        ];
        
        return $behaviors;
    }
    
    /**
     * Вход в систему
     * @return array
     * @throws BadRequestHttpException если неверные учетные данные
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $user = Yii::$app->user->identity;
            
            return [
                'token' => $user->getAuthKey(),
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
            ];
        }
        
        Yii::$app->response->statusCode = 400;
        return [
            'errors' => $model->getErrors(),
            'message' => 'Неверное имя пользователя или пароль',
        ];
    }
    
    /**
     * Выход из системы
     * @return array
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        
        return [
            'success' => true,
            'message' => 'Вы успешно вышли из системы',
        ];
    }
    
    /**
     * Получение информации о текущем пользователе
     * @return array
     * @throws UnauthorizedHttpException если пользователь не авторизован
     */
    public function actionMe()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->setStatusCode(401);
            return [
                'success' => false,
                'message' => 'Требуется аутентификация'
            ];
        }
        
        $user = Yii::$app->user->identity;
        
        return [
            'success' => true,
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'permissions' => $this->getUserPermissions($user),
        ];
    }
    
    /**
     * Обновление токена аутентификации
     * @return array
     * @throws UnauthorizedHttpException если пользователь не авторизован
     */
    public function actionRefreshToken()
    {
        if (Yii::$app->user->isGuest) {
            throw new UnauthorizedHttpException('Требуется аутентификация');
        }
        
        $user = Yii::$app->user->identity;
        $user->generateAuthKey();
        
        if (!$user->save()) {
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Не удалось обновить токен',
                'errors' => $user->getErrors(),
            ];
        }
        
        return [
            'token' => $user->getAuthKey(),
            'message' => 'Токен успешно обновлен',
        ];
    }
    
    /**
     * Получает список разрешений пользователя
     * @param User $user Пользователь
     * @return array
     */
    protected function getUserPermissions($user)
    {
        $auth = Yii::$app->authManager;
        $permissions = [];
        
        // Получаем все роли пользователя
        $roles = $auth->getRolesByUser($user->id);
        
        // Для каждой роли получаем разрешения
        foreach ($roles as $role) {
            $rolePermissions = $auth->getPermissionsByRole($role->name);
            foreach ($rolePermissions as $permission) {
                $permissions[$permission->name] = true;
            }
        }
        
        // Добавляем прямые разрешения пользователя
        $directPermissions = $auth->getPermissionsByUser($user->id);
        foreach ($directPermissions as $permission) {
            $permissions[$permission->name] = true;
        }
        
        return array_keys($permissions);
    }
    
    /**
     * CORS preflight request
     */
    public function actionOptions()
    {
        Yii::$app->response->setStatusCode(204);
        return [];
    }
} 