<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'api' => [
            'class' => 'backend\modules\api\Module',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            'name' => 'advanced-backend',
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
                'vue-app' => 'site/vue-app',
                'site/login-api' => 'site/login-api',
                // API module
                'api/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => 'api/<controller>/<action>',
                'api/<controller:[\w-]+>/<action:[\w-]+>' => 'api/<controller>/<action>',
                'api/<controller:[\w-]+>' => 'api/<controller>/index',
                
                // Специальные правила для API
                'api/auth/login' => 'api/auth/login',
                'api/auth/logout' => 'api/auth/logout',
                'api/auth/me' => 'api/auth/me',
                'api/auth/refresh-token' => 'api/auth/refresh-token',
                'api/auth/options' => 'api/auth/options',
                
                'api/client/restore/<id:\d+>' => 'api/client/restore',
                'api/client/clubs/<id:\d+>' => 'api/client/clubs',
                'api/client/stats' => 'api/client/stats',
                'api/client/export' => 'api/client/export',
                
                'api/club/restore/<id:\d+>' => 'api/club/restore',
                'api/club/clients/<id:\d+>' => 'api/club/clients',
                'api/club/stats' => 'api/club/stats',
                
                // Default routes
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
