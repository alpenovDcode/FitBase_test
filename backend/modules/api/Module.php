<?php

namespace backend\modules\api;

use Yii;

/**
 * api модуль для RESTful API
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // Отключаем сессии для API
        Yii::$app->user->enableSession = false;
        
        // Настраиваем компонент user для работы с токенами
        Yii::$app->user->enableAutoLogin = false;
        
        // Устанавливаем формат ответа JSON
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        // Логируем инициализацию модуля API
        \Yii::info('API module initialized', 'api');
    }
} 