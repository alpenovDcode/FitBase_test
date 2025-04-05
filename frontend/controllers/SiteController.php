<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

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
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Отображение Vue приложения
     *
     * @return string
     */
    public function actionVueApp()
    {
        $this->layout = false;
        
        $vueAppPath = Yii::getAlias('@frontend/web/vue-app.html');
        
        if (file_exists($vueAppPath)) {
            // Получаем содержимое HTML файла
            $htmlContent = file_get_contents($vueAppPath);
            
            // Определяем текущий домен для API запросов
            $currentDomain = Yii::$app->request->hostInfo;
            $apiDomain = strpos($currentDomain, ':21080') !== false 
                ? str_replace(':21080', ':8081', $currentDomain) 
                : $currentDomain;
            
            $apiConfig = "<script>
// Настройки API
window.API_CONFIG = {
    baseUrl: '{$apiDomain}/api',
    authLogin: '{$apiDomain}/api/auth/login',
    authLogout: '{$apiDomain}/api/auth/logout',
    authMe: '{$apiDomain}/api/auth/me'
};
console.log('API config:', window.API_CONFIG);
</script>";
            
            // Добавляем скрипт после открывающего тега <head>
            $htmlContent = preg_replace('/<head>/', '<head>' . "\n" . $apiConfig, $htmlContent);
            
            // Возвращаем скорректированный HTML
            return $htmlContent;
        }
        
        // Если файл не найден, выводим простое сообщение
        return '<h1>Vue приложение не найдено</h1>';
    }

    /**
     * Отображение главной страницы
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Авторизация пользователя.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

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
     * Выход текущего пользователя из системы.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Отображает страницу контактов.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Спасибо за обращение к нам. Мы ответим вам как можно скорее.');
            } else {
                Yii::$app->session->setFlash('error', 'Произошла ошибка при отправке вашего сообщения.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Отображает страницу "О нас".
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Регистрация пользователя.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Спасибо за регистрацию. Пожалуйста, проверьте вашу электронную почту для подтверждения.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Запрос на сброс пароля.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Проверьте вашу электронную почту для дальнейших инструкций.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'К сожалению, мы не можем сбросить пароль для указанного адреса электронной почты.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Сброс пароля.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Новый пароль сохранен.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Подтверждение адреса электронной почты
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Ваш email подтвержден!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'К сожалению, мы не можем подтвердить ваш аккаунт с предоставленным токеном.');
        return $this->goHome();
    }

    /**
     * Повторная отправка письма для подтверждения email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Проверьте вашу электронную почту для дальнейших инструкций.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'К сожалению, мы не можем повторно отправить письмо для подтверждения на указанный адрес электронной почты.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
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
