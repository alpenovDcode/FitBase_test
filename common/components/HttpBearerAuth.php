<?php

namespace common\components;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

/**
 * HttpBearerAuth реализует метод аутентификации HTTP Bearer.
 */
class HttpBearerAuth extends AuthMethod
{
    /**
     * @var string Название HTTP-заголовка, содержащего токен
     */
    public $header = 'Authorization';
    
    /**
     * @var string Шаблон для получения токена из заголовка
     */
    public $pattern = '/^Bearer\s+(.*?)$/';
    
    /**
     * @var string Сообщение об ошибке, если токен недействителен
     */
    public $tokenInvalidMessage = 'Недействительный токен аутентификации';

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);
        
        if ($authHeader !== null) {
            if (preg_match($this->pattern, $authHeader, $matches)) {
                $token = $matches[1];
                
                $identity = $user->loginByAccessToken($token, get_class($this));
                
                if ($identity === null) {
                    $this->handleFailure($response);
                }
                
                return $identity;
            }
        }
        
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', 'Bearer realm="api"');
    }

    /**
     * {@inheritdoc}
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException($this->tokenInvalidMessage);
    }
} 