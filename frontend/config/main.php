<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'modules' => [
        'api' => [
            'class' => 'frontend\modules\api\Module',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // Vue приложение
                'vue-app' => 'site/vue-app',
                
                // API метод авторизации
                'site/login-api' => 'site/login-api',
                
                // Устанавливаем прямые и простые правила для API
                'api/club' => 'api/club/index',
                'api/club/create' => 'api/club/create',
                'api/club/<id:\d+>' => 'api/club/view',
                'api/club/<id:\d+>/update' => 'api/club/update',
                'api/club/<id:\d+>/delete' => 'api/club/delete',
                'api/club/stats' => 'api/club/stats',
                'api/club/<id:\d+>/restore' => 'api/club/restore',
                'api/club/<id:\d+>/clients' => 'api/club/clients',
                
                // Правила для REST методов
                'PUT,PATCH api/club/<id:\d+>' => 'api/club/update',
                'DELETE api/club/<id:\d+>' => 'api/club/delete',
                'POST api/club' => 'api/club/create',
                
                'api/client' => 'api/client/index',
                'api/client/create' => 'api/client/create',
                'api/client/<id:\d+>' => 'api/client/view',
                'api/client/<id:\d+>/update' => 'api/client/update',
                'api/client/<id:\d+>/delete' => 'api/client/delete',
                'api/client/stats' => 'api/client/stats',
                'api/client/<id:\d+>/restore' => 'api/client/restore',
                'api/client/<id:\d+>/clubs' => 'api/client/clubs',
                'api/client/export' => 'api/client/export',
                
                // Правила для REST методов
                'PUT,PATCH api/client/<id:\d+>' => 'api/client/update',
                'DELETE api/client/<id:\d+>' => 'api/client/delete',
                'POST api/client' => 'api/client/create',
                
                // Специальные правила для CORS
                'OPTIONS api/club' => 'api/club/options',
                'OPTIONS api/club/<id:\d+>' => 'api/club/options',
                'OPTIONS api/club/create' => 'api/club/options',
                'OPTIONS api/club/<id:\d+>/update' => 'api/club/options',
                'OPTIONS api/club/<id:\d+>/delete' => 'api/club/options',
                'OPTIONS PUT api/club/<id:\d+>' => 'api/club/options',
                'OPTIONS DELETE api/club/<id:\d+>' => 'api/club/options',
                
                'OPTIONS api/client' => 'api/client/options',
                'OPTIONS api/client/<id:\d+>' => 'api/client/options',
                'OPTIONS api/client/create' => 'api/client/options',
                'OPTIONS api/client/<id:\d+>/update' => 'api/client/options',
                'OPTIONS api/client/<id:\d+>/delete' => 'api/client/options',
                'OPTIONS PUT api/client/<id:\d+>' => 'api/client/options',
                'OPTIONS DELETE api/client/<id:\d+>' => 'api/client/options',
                
                // Специальные правила для Auth
                'api/auth/login' => 'api/auth/login',
                'api/auth/logout' => 'api/auth/logout',
                'api/auth/me' => 'api/auth/me',
                'api/auth/refresh-token' => 'api/auth/refresh-token',
                'OPTIONS api/auth/login' => 'api/auth/options',
                'OPTIONS api/auth/logout' => 'api/auth/options',
                'OPTIONS api/auth/me' => 'api/auth/options',
                'OPTIONS api/auth/refresh-token' => 'api/auth/options',
                
                // Default routes
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
