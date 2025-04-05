<?php

namespace backend\controllers;

use common\models\Client;
use common\models\search\ClientSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ClientController реализует CRUD-операции для модели Client.
 */
class ClientController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'restore' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображает список всех Client моделей.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ClientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Отображает отдельную модель Client.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если модель не найдена
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Создает новую модель Client.
     * Если создание прошло успешно, браузер будет перенаправлен на страницу 'view'.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Client();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Клиент успешно создан');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Обновляет существующую модель Client.
     * Если обновление прошло успешно, браузер будет перенаправлен на страницу 'view'.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если модель не найдена
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Клиент успешно обновлен');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаляет существующую модель Client.
     * Если удаление прошло успешно, браузер будет перенаправлен на страницу 'index'.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если модель не найдена
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Клиент успешно удален');
        return $this->redirect(['index']);
    }
    
    /**
     * Восстанавливает мягко удаленную модель Client.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если модель не найдена
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id, true);
        $model->restore();

        Yii::$app->session->setFlash('success', 'Клиент успешно восстановлен');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Находит модель Client по её первичному ключу.
     * Если модель не найдена, будет выброшено исключение 404 HTTP.
     * @param integer $id
     * @param bool $withDeleted Искать ли среди удаленных записей
     * @return Client загруженная модель
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id, $withDeleted = false)
    {
        $query = Client::find()->with(['club'])->where(['client.id' => $id]);
        
        if ($withDeleted) {
            $query->withDeleted();
        }
        
        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }
}
