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
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
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
            'rules' => [
                'clubs' => 'club/index',
                'clubs/<id:\d+>' => 'club/view',
                'clubs/create' => 'club/create',
                'clubs/<id:\d+>/update' => 'club/update',
                'clubs/<id:\d+>/delete' => 'club/delete',
                'clients' => 'client/index',
                'clients/<id:\d+>' => 'client/view',
                'clients/create' => 'client/create',
                'clients/<id:\d+>/update' => 'client/update',
                'clients/<id:\d+>/delete' => 'client/delete',
            ],
        ],
    ],
    'params' => $params,
];
