<?php

namespace frontend\modules\api;

use Yii;

/**
 * API модуль для frontend части приложения
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'frontend\modules\api\controllers';

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
        \Yii::info('Frontend API module initialized', 'api');
    }
} 