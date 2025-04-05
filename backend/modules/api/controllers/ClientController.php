<?php

namespace backend\modules\api\controllers;

use common\models\Client;
use common\models\search\ClientSearch;
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
 * REST API контроллер для модели Client
 */
class ClientController extends ActiveController
{
    /**
     * @var string Модель для контроллера
     */
    public $modelClass = 'common\models\Client';
    
    /**
     * @var array Разрешенные поля для сортировки
     */
    protected $allowedSortFields = ['id', 'name', 'surname', 'phone', 'email', 'birth_date', 'gender', 'created_at', 'updated_at'];
    
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
                'clubs' => ['GET'],
                'stats' => ['GET'],
                'export' => ['GET'],
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
     * Проверяет доступ на создание клиента
     * @param string $action ID действия
     * @param mixed $model Модель или класс модели
     * @param array $params Дополнительные параметры
     * @throws ForbiddenHttpException если у пользователя нет прав
     */
    public function checkCreateAccess($action, $model, $params = [])
    {
        if (!Yii::$app->user->can('createClient')) {
            throw new ForbiddenHttpException('У вас нет прав для создания клиента');
        }
    }
    
    /**
     * Проверяет доступ на обновление клиента
     * @param string $action ID действия
     * @param mixed $model Модель или класс модели
     * @param array $params Дополнительные параметры
     * @throws ForbiddenHttpException если у пользователя нет прав
     */
    public function checkUpdateAccess($action, $model, $params = [])
    {
        if (!Yii::$app->user->can('updateClient')) {
            throw new ForbiddenHttpException('У вас нет прав для обновления клиента');
        }
    }
    
    /**
     * Проверяет доступ на удаление клиента
     * @param string $action ID действия
     * @param mixed $model Модель или класс модели
     * @param array $params Дополнительные параметры
     * @throws ForbiddenHttpException если у пользователя нет прав
     */
    public function checkDeleteAccess($action, $model, $params = [])
    {
        if (!Yii::$app->user->can('deleteClient')) {
            throw new ForbiddenHttpException('У вас нет прав для удаления клиента');
        }
    }

    /**
     * Подготавливает провайдер данных для action index
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new ClientSearch();
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
        
        // Устанавливаем размер страницы, если он указан
        if (isset($params['per-page']) && is_numeric($params['per-page'])) {
            $pageSize = (int)$params['per-page'];
            if ($pageSize > 0 && $pageSize <= 100) {
                $searchModel->setPageSize($pageSize);
            }
        }
        
        return $searchModel->search($params);
    }
    
    /**
     * Восстанавливает удаленную запись
     * @param int $id ID записи
     * @return Client
     * @throws NotFoundHttpException если запись не найдена
     * @throws ForbiddenHttpException если у пользователя нет прав
     * @throws ServerErrorHttpException если не удалось восстановить запись
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id, true);
        
        if (!Yii::$app->user->can('restoreClient')) {
            throw new ForbiddenHttpException('У вас нет прав для восстановления клиента');
        }
        
        if (!$model->restore()) {
            throw new ServerErrorHttpException('Не удалось восстановить клиента');
        }
        
        return $model;
    }
    
    /**
     * Возвращает клубы клиента
     * @param int $id ID клиента
     * @return array
     * @throws NotFoundHttpException если клиент не найден
     */
    public function actionClubs($id)
    {
        $model = $this->findModel($id);
        $clubs = $model->getClub()->one();
        return $clubs ? [$clubs] : [];
    }
    
    /**
     * Возвращает статистику по клиентам
     * @return array
     */
    public function actionStats()
    {
        $totalClients = Client::find()->count();
        $deletedClients = Client::find()->onlyDeleted()->count();
        $activeClients = $totalClients - $deletedClients;
        
        $maleCount = Client::find()->andWhere(['gender' => Client::GENDER_MALE])->count();
        $femaleCount = Client::find()->andWhere(['gender' => Client::GENDER_FEMALE])->count();
        
        $stats = [
            'total' => (int)$totalClients,
            'active' => (int)$activeClients,
            'deleted' => (int)$deletedClients,
            'created_today' => (int)Client::find()
                ->andWhere(['>=', 'DATE(created_at)', date('Y-m-d')])
                ->count(),
            'gender' => [
                'male' => (int)$maleCount,
                'female' => (int)$femaleCount,
                'undefined' => (int)($activeClients - $maleCount - $femaleCount),
            ],
            'clients_by_age' => $this->getClientsByAge(),
        ];
        
        return $stats;
    }
    
    /**
     * Возвращает распределение клиентов по возрасту
     * @return array
     */
    protected function getClientsByAge()
    {
        $result = [
            '0-18' => 0,
            '19-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-60' => 0,
            '60+' => 0,
            'undefined' => 0,
        ];
        
        $clients = Client::find()->all();
        
        foreach ($clients as $client) {
            $age = $client->getAge();
            
            if ($age === null) {
                $result['undefined']++;
            } elseif ($age <= 18) {
                $result['0-18']++;
            } elseif ($age <= 25) {
                $result['19-25']++;
            } elseif ($age <= 35) {
                $result['26-35']++;
            } elseif ($age <= 45) {
                $result['36-45']++;
            } elseif ($age <= 60) {
                $result['46-60']++;
            } else {
                $result['60+']++;
            }
        }
        
        return $result;
    }
    
    /**
     * Экспортирует данные клиентов в JSON формате
     * @return array
     * @throws ForbiddenHttpException если у пользователя нет прав
     */
    public function actionExport()
    {
        if (!Yii::$app->user->can('exportClients')) {
            throw new ForbiddenHttpException('У вас нет прав для экспорта данных клиентов');
        }
        
        $clients = Client::find()->all();
        $result = [];
        
        foreach ($clients as $client) {
            $club = $client->getClub()->one();
            
            $result[] = [
                'id' => $client->id,
                'name' => $client->name,
                'surname' => $client->surname,
                'phone' => $client->phone,
                'email' => $client->email,
                'birth_date' => $client->birth_date,
                'gender' => $client->getGenderText(),
                'club' => $club ? $club->name : null,
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at,
            ];
        }
        
        return $result;
    }
    
    /**
     * Находит модель Client по её первичному ключу
     * @param int $id ID записи
     * @param bool $withDeleted Искать ли среди удаленных записей
     * @return Client
     * @throws NotFoundHttpException если запись не найдена
     */
    protected function findModel($id, $withDeleted = false)
    {
        $query = Client::find()->where(['id' => $id]);
        
        if ($withDeleted) {
            $query->withDeleted();
        }
        
        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }
} 