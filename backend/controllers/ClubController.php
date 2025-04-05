<?php

namespace backend\controllers;

use common\models\Club;
use common\models\search\ClubSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ClubController реализует CRUD-операции для модели Club.
 */
class ClubController extends Controller
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
     * Отображает список всех Club моделей.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ClubSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Отображает отдельную модель Club.
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
     * Создает новую модель Club.
     * Если создание прошло успешно, браузер будет перенаправлен на страницу 'view'.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Club();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Клуб успешно создан');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Обновляет существующую модель Club.
     * Если обновление прошло успешно, браузер будет перенаправлен на страницу 'view'.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если модель не найдена
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Клуб успешно обновлен');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаляет существующую модель Club.
     * Если удаление прошло успешно, браузер будет перенаправлен на страницу 'index'.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если модель не найдена
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Клуб успешно удален');
        return $this->redirect(['index']);
    }
    
    /**
     * Восстанавливает мягко удаленную модель Club.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException если модель не найдена
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id, true);
        $model->restore();

        Yii::$app->session->setFlash('success', 'Клуб успешно восстановлен');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Находит модель Club по её первичному ключу.
     * Если модель не найдена, будет выброшено исключение 404 HTTP.
     * @param integer $id
     * @param bool $withDeleted Искать ли среди удаленных записей
     * @return Club загруженная модель
     * @throws NotFoundHttpException если модель не найдена
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
