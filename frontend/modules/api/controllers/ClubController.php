<?php

namespace frontend\modules\api\controllers;

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
    public $modelClass = Club::class;
    
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
                'clients' => ['GET'],
                'stats' => ['GET'],
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
        // Временно отключаем проверку прав
        return true;
        
        // Исходный код проверки прав, закомментирован
        // if (!Yii::$app->user->can('createClub')) {
        //    throw new ForbiddenHttpException('У вас нет прав на создание клуба');
        // }
        // return true;
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
        // Временно отключаем проверку прав
        return true;
        
        // Исходный код проверки прав, закомментирован
        // if ($action === 'delete' && !Yii::$app->user->can('deleteClub')) {
        //    throw new ForbiddenHttpException('У вас нет прав на удаление клуба');
        // }
        // return true;
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
        // Временно отключаем проверку прав
        return true;
        
        // Исходный код проверки прав, закомментирован
        // if (!Yii::$app->user->can('updateClub')) {
        //    throw new ForbiddenHttpException('У вас нет прав на редактирование клуба');
        // }
        // return true;
    }
    
    /**
     * Удаляет клуб (мягкое удаление)
     * @param int $id ID клуба
     * @return \common\models\Club
     * @throws NotFoundHttpException если клуб не найден
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Временно отключаем проверку прав
        // if (!Yii::$app->user->can('deleteClub')) {
        //     throw new ForbiddenHttpException('У вас нет прав на удаление клуба');
        // }
        
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        
        if ($model->save(false)) {
            return $model;
        }
        
        throw new ServerErrorHttpException('Ошибка при удалении клуба');
    }

    /**
     * CORS preflight request
     */
    public function actionOptions()
    {
        Yii::$app->response->setStatusCode(204);
        return [];
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
        
        // Временно отключаем проверку прав
        // if (!Yii::$app->user->can('restoreClub')) {
        //     throw new ForbiddenHttpException('У вас нет прав для восстановления клуба');
        // }
        
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

    /**
     * Проверяет, есть ли у клуба связанные клиенты
     * @param Club $club Модель клуба
     * @return bool true если есть клиенты, false если нет
     */
    protected function clubHasClients($club)
    {
        return $club->getClientsCount() > 0;
    }

    /**
     * Создает новый клуб
     * @return \common\models\Club
     * @throws \yii\web\BadRequestHttpException если данные некорректны
     */
    public function actionCreate()
    {
        $model = new Club();
        $model->load(Yii::$app->request->post(), '');
        
        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);
            return $model;
        }
        
        Yii::$app->response->setStatusCode(422);
        return ['errors' => $model->errors];
    }
    
    /**
     * Обновляет существующий клуб
     * @param int $id ID клуба
     * @return \common\models\Club
     * @throws \yii\web\NotFoundHttpException если клуб не найден
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
     * Список всех клубов
     * @return \common\models\Club[]
     */
    public function actionIndex()
    {
        $query = Club::find();
        
        // Обработка фильтров
        $params = Yii::$app->request->queryParams;
        
        // Фильтр по названию
        if (!empty($params['name'])) {
            $query->andWhere(['like', 'name', $params['name']]);
        }
        
        // Фильтр по адресу
        if (!empty($params['address'])) {
            $query->andWhere(['like', 'address', $params['address']]);
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
            // По умолчанию сортируем по имени
            $query->orderBy(['name' => SORT_ASC]);
        }
        
        return $query->all();
    }
    
    /**
     * Просмотр клуба по ID
     * @param int $id ID клуба
     * @return \common\models\Club
     * @throws \yii\web\NotFoundHttpException если клуб не найден
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }
} 