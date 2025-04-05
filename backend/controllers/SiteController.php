<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Контроллер сайта
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'vue-app', 'login-api'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'login-api' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Отображает главную страницу.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Действие для входа в систему.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Действие для выхода из системы.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Отображение Vue приложения
     *
     * @return string
     */
    public function actionVueApp()
    {
        $this->layout = false;
        
        // Проверяем наличие файла в нескольких возможных местах
        $possiblePaths = [
            Yii::getAlias('@frontend/web/vue-app.html'),
            Yii::getAlias('@backend/web/vue-app.html'),
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                // Получаем содержимое HTML файла
                $htmlContent = file_get_contents($path);
                
                // Исправляем пути к стилям и скриптам, учитывая что они должны начинаться с /frontend/web
                $htmlContent = preg_replace('/(href=")(?!http|\/\/|\/frontend)/i', '$1/frontend/web/', $htmlContent);
                $htmlContent = preg_replace('/(src=")(?!http|\/\/|\/frontend)/i', '$1/frontend/web/', $htmlContent);
                
                // Убеждаемся, что Vue.js и Axios загружаются из CDN
                if (strpos($htmlContent, 'vue.js') === false) {
                    $htmlContent = str_replace('</head>', 
                        '<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>' . "\n" . '</head>', 
                        $htmlContent);
                }
                
                if (strpos($htmlContent, 'axios') === false) {
                    $htmlContent = str_replace('</head>', 
                        '<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>' . "\n" . '</head>', 
                        $htmlContent);
                }
                
                // Проверяем наличие настройки baseUrl и добавляем его, если отсутствует
                if (strpos($htmlContent, 'apiBaseUrl') === false) {
                    $scriptTag = '<script>
// Настройка базового URL для API запросов
const apiBaseUrl = \'/frontend/web\';

// Настройка Axios
axios.defaults.baseURL = window.location.origin + apiBaseUrl;
console.log(\'Axios base URL:\', axios.defaults.baseURL);
</script>';
                    
                    // Добавляем скрипт после открывающего тега body
                    $htmlContent = preg_replace('/(<body[^>]*>)/', '$1' . "\n" . $scriptTag, $htmlContent);
                }
                
                // Возвращаем скорректированный HTML
                return $htmlContent;
            }
        }
        
        // Если файл не найден, создаем базовую структуру
        return $this->renderContent($this->generateBasicVueApp());
    }
    
    /**
     * Генерирует базовую HTML структуру для Vue приложения
     * 
     * @return string
     */
    private function generateBasicVueApp()
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitBaseTest</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="/frontend/web/css/site-vue.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>window.baseUrl = "{$this->request->baseUrl}";</script>
</head>
<body>
    <div id="app" v-cloak>
        <h1>FitBaseTest</h1>
        <p>{{ message }}</p>
    </div>
    
    <script>
    // Настройка Axios
    axios.defaults.baseURL = window.location.origin;
    
    // Инициализация Vue
    new Vue({
        el: '#app',
        data: {
            message: 'Загрузка приложения...'
        },
        created() {
            console.log('Vue приложение инициализировано');
        }
    });
    </script>
</body>
</html>
HTML;
    }

    /**
     * API метод авторизации для поддержки прямого логина
     *
     * @return \yii\web\Response
     */
    public function actionLoginApi()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $user = Yii::$app->user->identity;
            return [
                'success' => true,
                'token' => $user->getAuthKey(),
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ]
            ];
        } else {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'message' => 'Ошибка входа. Проверьте логин и пароль.',
                'errors' => $model->getErrors()
            ];
        }
    }
}
