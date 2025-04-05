<?php

namespace frontend\modules\api\controllers;

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
    public $modelClass = Client::class;
    
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
            'except' => ['options', 'index'],
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
                'options' => ['OPTIONS'],
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
        
        // Отключаем все стандартные действия
        // И вместо них используем собственные реализации
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        
        return $actions;
    }
    
    /**
     * Проверка прав доступа на создание объекта
     * @param string $action действие
     * @return bool разрешено ли действие
     */
    public function checkCreateAccess($action)
    {
        if (!Yii::$app->user->can('createClient')) {
            throw new ForbiddenHttpException('У вас нет прав на создание клиента');
        }
        return true;
    }
    
    /**
     * Проверка прав доступа на операции с объектом
     * @param string $action действие
     * @param object $model модель
     * @param array $params дополнительные параметры
     * @return bool разрешено ли действие
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // Проверка прав доступа
        if ($action === 'delete' && !Yii::$app->user->can('deleteClient')) {
            throw new ForbiddenHttpException('У вас нет прав на удаление клиента');
        }
        return true;
    }

    /**
     * Проверка прав доступа на обновление объекта
     * @param string $action действие
     * @param object $model модель
     * @param array $params дополнительные параметры
     * @return bool разрешено ли действие
     */
    public function checkUpdateAccess($action, $model = null, $params = [])
    {
        if (!Yii::$app->user->can('updateClient')) {
            throw new ForbiddenHttpException('У вас нет прав на редактирование клиента');
        }
        return true;
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
     * Удаляет клиента (мягкое удаление)
     * @param int $id ID клиента
     * @return \common\models\Client
     * @throws NotFoundHttpException если клиент не найден
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if (!Yii::$app->user->can('deleteClient')) {
            throw new ForbiddenHttpException('У вас нет прав на удаление клиента');
        }
        
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        
        if ($model->save(false)) {
            return $model;
        }
        
        throw new ServerErrorHttpException('Ошибка при удалении клиента');
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

    /**
     * Предварительный запрос CORS
     */
    public function actionOptions()
    {
        Yii::$app->response->setStatusCode(204);
        return [];
    }

    /**
     * Проверяет, есть ли связанные с клиентом данные, которые могут препятствовать удалению
     * @param Client $client Модель клиента
     * @return bool true если есть связанные данные, false если можно безопасно удалить
     */
    protected function clientHasRelatedData($client)
    {
        // В данном случае просто заглушка, в реальной системе здесь будет проверка связей
        // Например, проверка на наличие активных абонементов, занятий и т.д.
        return false;
    }

    /**
     * Создает нового клиента
     * @return \common\models\Client
     * @throws \yii\web\BadRequestHttpException если данные некорректны
     */
    public function actionCreate()
    {
        $model = new Client();
        $model->load(Yii::$app->request->post(), '');
        
        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);
            return $model;
        }
        
        Yii::$app->response->setStatusCode(422);
        return ['errors' => $model->errors];
    }
    
    /**
     * Обновляет существующего клиента
     * @param int $id ID клиента
     * @return \common\models\Client
     * @throws \yii\web\NotFoundHttpException если клиент не найден
     * @throws \yii\web\BadRequestHttpException если данные некорректны
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->post(), '');
        
        if ($model->save()) {
            return $model;
        }
        
        Yii::$app->response->setStatusCode(422);
        return ['errors' => $model->errors];
    }

    /**
     * Список всех клиентов
     * @return \common\models\Client[]
     */
    public function actionIndex()
    {
        $query = Client::find();
        
        // Обработка фильтров
        $params = Yii::$app->request->queryParams;
        
        // Фильтр по имени и фамилии
        if (!empty($params['name'])) {
            $query->andWhere(['or', 
                ['like', 'name', $params['name']], 
                ['like', 'surname', $params['name']]
            ]);
        }
        
        // Фильтр по полу
        if (!empty($params['gender'])) {
            $query->andWhere(['gender' => $params['gender']]);
        }
        
        // Фильтр по дате рождения (с)
        if (!empty($params['birthDateFrom'])) {
            $query->andWhere(['>=', 'birth_date', date('Y-m-d', strtotime($params['birthDateFrom']))]);
        }
        
        // Фильтр по дате рождения (по)
        if (!empty($params['birthDateTo'])) {
            $query->andWhere(['<=', 'birth_date', date('Y-m-d', strtotime($params['birthDateTo']))]);
        }
        
        // Фильтр по удаленным записям
        if (isset($params['showDeleted']) && $params['showDeleted']) {
            $query->withDeleted();
        }
        
        // Параметры пагинации
        $page = Yii::$app->request->get('page', 1);
        $pageSize = Yii::$app->request->get('per-page', 50);
        
        // Ограничиваем максимальное количество на странице
        $pageSize = min(100, $pageSize);
        
        // Применяем пагинацию
        $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize);
        
        // Применяем сортировку
        $sort = Yii::$app->request->get('sort');
        if ($sort) {
            $sortDir = substr($sort, 0, 1) === '-' ? SORT_DESC : SORT_ASC;
            $sortField = $sortDir === SORT_DESC ? substr($sort, 1) : $sort;
            
            if (in_array($sortField, $this->allowedSortFields)) {
                $query->orderBy([$sortField => $sortDir]);
            }
        } else {
            // По умолчанию сортируем по убыванию ID
            $query->orderBy(['id' => SORT_DESC]);
        }
        
        return $query->all();
    }
    
    /**
     * Просмотр клиента по ID
     * @param int $id ID клиента
     * @return \common\models\Client
     * @throws \yii\web\NotFoundHttpException если клиент не найден
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }
} 