<?php

namespace backend\modules\api\controllers;

use common\models\Club;
use common\models\search\ClubSearch;
use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use common\components\HttpBearerAuth;

/**
 * REST API контроллер для модели Club
 */
class ClubController extends ActiveController
{
    /**
     * @var string Модель для контроллера
     */
    public $modelClass = 'common\models\Club';
    
    /**
     * @var array Разрешенные поля для сортировки
     */
    protected $allowedSortFields = ['id', 'name', 'address', 'phone', 'email', 'created_at', 'updated_at'];
    
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
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        
        // Настройка аутентификации
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'],
        ];
        
        // Настройка HTTP методов
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
                'restore' => ['POST'],
                'clients' => ['GET'],
                'stats' => ['GET'],
            ],
        ];
        
        return $behaviors;
    }
    
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        
        // Настраиваем стандартные действия
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['create']['checkAccess'] = [$this, 'checkCreateAccess'];
        $actions['update']['checkAccess'] = [$this, 'checkUpdateAccess'];
        $actions['delete']['checkAccess'] = [$this, 'checkDeleteAccess'];
        
        return $actions;
    }
    
    /**
     * Проверяет доступ на создание клуба
     * @param string $action ID действия
     * @param mixed $model Модель или класс модели
     * @param array $params Дополнительные параметры
     * @throws ForbiddenHttpException если у пользователя нет прав
     */
    public function checkCreateAccess($action, $model, $params = [])
    {
        if (!Yii::$app->user->can('createClub')) {
            throw new ForbiddenHttpException('У вас нет прав для создания клуба');
        }
    }
    
    /**
     * Проверяет доступ на обновление клуба
     * @param string $action ID действия
     * @param mixed $model Модель или класс модели
     * @param array $params Дополнительные параметры
     * @throws ForbiddenHttpException если у пользователя нет прав
     */
    public function checkUpdateAccess($action, $model, $params = [])
    {
        if (!Yii::$app->user->can('updateClub')) {
            throw new ForbiddenHttpException('У вас нет прав для обновления клуба');
        }
    }
    
    /**
     * Проверяет доступ на удаление клуба
     * @param string $action ID действия
     * @param mixed $model Модель или класс модели
     * @param array $params Дополнительные параметры
     * @throws ForbiddenHttpException если у пользователя нет прав
     */
    public function checkDeleteAccess($action, $model, $params = [])
    {
        if (!Yii::$app->user->can('deleteClub')) {
            throw new ForbiddenHttpException('У вас нет прав для удаления клуба');
        }
    }

    /**
     * Подготавливает провайдер данных для action index
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new ClubSearch();
        $params = Yii::$app->request->queryParams;
        
        // Обработка параметров сортировки
        if (isset($params['sort'])) {
            $sortParams = explode(',', $params['sort']);
            $allowedSort = [];
            
            foreach ($sortParams as $sortParam) {
                $field = ltrim($sortParam, '-');
                if (in_array($field, $this->allowedSortFields)) {
                    $allowedSort[] = $sortParam;
                }
            }
            
            if (!empty($allowedSort)) {
                $params['sort'] = implode(',', $allowedSort);
            } else {
                unset($params['sort']);
            }
        }
        
        return $searchModel->search($params);
    }
    
    /**
     * Восстанавливает удаленную запись
     * @param int $id ID записи
     * @return Club
     * @throws NotFoundHttpException если запись не найдена
     * @throws ForbiddenHttpException если у пользователя нет прав
     * @throws ServerErrorHttpException если не удалось восстановить запись
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id, true);
        
        if (!Yii::$app->user->can('restoreClub')) {
            throw new ForbiddenHttpException('У вас нет прав для восстановления клуба');
        }
        
        if (!$model->restore()) {
            throw new ServerErrorHttpException('Не удалось восстановить клуб');
        }
        
        return $model;
    }
    
    /**
     * Возвращает клиентов клуба
     * @param int $id ID клуба
     * @return array
     * @throws NotFoundHttpException если клуб не найден
     */
    public function actionClients($id)
    {
        $model = $this->findModel($id);
        $clients = $model->getClients()->all();
        return $clients;
    }
    
    /**
     * Возвращает статистику по клубам
     * @return array
     */
    public function actionStats()
    {
        $totalClubs = Club::find()->count();
        $deletedClubs = Club::find()->onlyDeleted()->count();
        $activeClubs = $totalClubs - $deletedClubs;
        
        $stats = [
            'total' => (int)$totalClubs,
            'active' => (int)$activeClubs,
            'deleted' => (int)$deletedClubs,
            'created_today' => (int)Club::find()
                ->andWhere(['>=', 'DATE(created_at)', date('Y-m-d')])
                ->count(),
            'clubs_by_clients' => $this->getClubsByClientsCount(),
        ];
        
        return $stats;
    }
    
    /**
     * Возвращает распределение клубов по количеству клиентов
     * @return array
     */
    protected function getClubsByClientsCount()
    {
        $result = [];
        $clubs = Club::find()->all();
        
        foreach ($clubs as $club) {
            $clientsCount = $club->getClientsCount();
            $range = $this->getClientCountRange($clientsCount);
            
            if (!isset($result[$range])) {
                $result[$range] = 0;
            }
            
            $result[$range]++;
        }
        
        return $result;
    }
    
    /**
     * Возвращает диапазон количества клиентов для статистики
     * @param int $count Количество клиентов
     * @return string
     */
    protected function getClientCountRange($count)
    {
        if ($count == 0) {
            return '0';
        } elseif ($count <= 10) {
            return '1-10';
        } elseif ($count <= 50) {
            return '11-50';
        } elseif ($count <= 100) {
            return '51-100';
        } else {
            return '100+';
        }
    }
    
    /**
     * Находит модель Club по её первичному ключу
     * @param int $id ID записи
     * @param bool $withDeleted Искать ли среди удаленных записей
     * @return Club
     * @throws NotFoundHttpException если запись не найдена
     */
    protected function findModel($id, $withDeleted = false)
    {
        $query = Club::find()->where(['id' => $id]);
        
        if ($withDeleted) {
            $query->withDeleted();
        }
        
        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }
} 