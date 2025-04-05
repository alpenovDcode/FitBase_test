<?php

namespace common\models\search;

use common\models\Client;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * ClientSearch представляет модель для поиска по базе данных Client
 */
class ClientSearch extends Client
{
    /**
     * @var bool Флаг для включения удаленных записей в результаты поиска
     */
    public $showDeleted = false;
    
    /**
     * @var string Поле для поиска по нескольким атрибутам
     */
    public $globalSearch;
    
    /**
     * @var string Поле для полного имени (имя + фамилия)
     */
    public $fullName;
    
    /**
     * @var string Поле для фильтрации по дате рождения от
     */
    public $birthDateFrom;
    
    /**
     * @var string Поле для фильтрации по дате рождения до
     */
    public $birthDateTo;
    
    /**
     * @var string Поле для фильтрации по дате создания от
     */
    public $createdAtFrom;
    
    /**
     * @var string Поле для фильтрации по дате создания до
     */
    public $createdAtTo;
    
    /**
     * @var string Название клуба для поиска
     */
    public $club_name;
    
    /**
     * @var int Количество записей на странице
     */
    protected $pageSize = 20;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'club_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['name', 'surname', 'phone', 'email', 'gender', 'address', 'globalSearch', 'fullName', 'club_name'], 'string'],
            [['birth_date', 'created_at', 'updated_at', 'deleted_at', 
              'birthDateFrom', 'birthDateTo', 'createdAtFrom', 'createdAtTo'], 'safe'],
            [['showDeleted'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // Пропускаем реализацию сценариев в родительском классе
        return Model::scenarios();
    }
    
    /**
     * Устанавливает размер страницы
     * @param int $pageSize Количество записей на страницу
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = (int)$pageSize;
        return $this;
    }

    /**
     * Создает провайдера данных с примененным поиском
     *
     * @param array $params Параметры поиска
     * @param array $options Дополнительные опции
     * @return ActiveDataProvider
     */
    public function search($params, $options = [])
    {
        $query = Client::find();
        $pageSize = isset($options['pageSize']) ? (int)$options['pageSize'] : $this->pageSize;

        // Настройка сортировки и пагинации
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'surname' => SORT_ASC,
                    'name' => SORT_ASC,
                ],
                'attributes' => $this->getSortAttributes(),
            ],
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        // Загрузка параметров поиска и валидация
        $this->load($params);
        if (!$this->validate()) {
            // Если данные невалидны, возвращаем провайдер без фильтрации
            return $dataProvider;
        }
        
        // JOIN с таблицей клубов для возможности поиска и сортировки
        $query->joinWith(['club' => function ($query) {
            $query->from(['club' => 'club']);
        }], false);
        
        // Показывать удаленные записи
        if ($this->showDeleted) {
            $query->withDeleted();
        }

        // Фильтрация по атрибутам
        $query->andFilterWhere([
            'client.id' => $this->id,
            'client.club_id' => $this->club_id,
            'client.gender' => $this->gender,
            'client.created_by' => $this->created_by,
            'client.updated_by' => $this->updated_by,
            'client.deleted_by' => $this->deleted_by,
        ]);
        
        // Фильтрация по названию клуба
        if (!empty($this->club_name)) {
            $query->andFilterWhere(['like', 'club.name', $this->club_name]);
        }
        
        // Фильтрация по дате рождения
        if ($this->birthDateFrom) {
            $query->andFilterWhere(['>=', 'client.birth_date', date('Y-m-d', strtotime($this->birthDateFrom))]);
        }
        
        if ($this->birthDateTo) {
            $query->andFilterWhere(['<=', 'client.birth_date', date('Y-m-d', strtotime($this->birthDateTo))]);
        }
        
        if ($this->birth_date) {
            $query->andFilterWhere(['=', 'client.birth_date', date('Y-m-d', strtotime($this->birth_date))]);
        }
        
        // Фильтрация по дате создания
        if ($this->createdAtFrom) {
            $query->andFilterWhere(['>=', 'DATE(client.created_at)', date('Y-m-d', strtotime($this->createdAtFrom))]);
        }
        
        if ($this->createdAtTo) {
            $query->andFilterWhere(['<=', 'DATE(client.created_at)', date('Y-m-d', strtotime($this->createdAtTo))]);
        }
        
        // Фильтрация по конкретной дате
        if ($this->created_at) {
            $query->andFilterWhere(['=', new Expression('DATE(client.created_at)'), date('Y-m-d', strtotime($this->created_at))]);
        }
        
        if ($this->updated_at) {
            $query->andFilterWhere(['=', new Expression('DATE(client.updated_at)'), date('Y-m-d', strtotime($this->updated_at))]);
        }
        
        if ($this->deleted_at) {
            $query->andFilterWhere(['=', new Expression('DATE(client.deleted_at)'), date('Y-m-d', strtotime($this->deleted_at))]);
        }

        // Фильтрация по текстовым полям
        $query->andFilterWhere(['like', 'client.name', $this->name])
            ->andFilterWhere(['like', 'client.surname', $this->surname])
            ->andFilterWhere(['like', 'client.phone', $this->phone])
            ->andFilterWhere(['like', 'client.email', $this->email])
            ->andFilterWhere(['like', 'client.address', $this->address]);
            
        // Поиск по полному имени
        if (!empty($this->fullName)) {
            $query->andWhere([
                'or',
                ['like', 'CONCAT(client.surname, " ", client.name)', $this->fullName],
                ['like', 'CONCAT(client.name, " ", client.surname)', $this->fullName],
            ]);
        }
            
        // Глобальный поиск по нескольким атрибутам
        if (!empty($this->globalSearch)) {
            $query->andWhere([
                'or',
                ['like', 'client.name', $this->globalSearch],
                ['like', 'client.surname', $this->globalSearch],
                ['like', 'client.phone', $this->globalSearch],
                ['like', 'client.email', $this->globalSearch],
                ['like', 'client.address', $this->globalSearch],
                ['like', 'club.name', $this->globalSearch],
            ]);
        }

        return $dataProvider;
    }
    
    /**
     * Возвращает атрибуты для сортировки
     * @return array
     */
    protected function getSortAttributes()
    {
        return [
            'name' => [
                'asc' => ['client.name' => SORT_ASC],
                'desc' => ['client.name' => SORT_DESC],
            ],
            'surname' => [
                'asc' => ['client.surname' => SORT_ASC],
                'desc' => ['client.surname' => SORT_DESC],
            ],
            'phone' => [
                'asc' => ['client.phone' => SORT_ASC],
                'desc' => ['client.phone' => SORT_DESC],
            ],
            'email' => [
                'asc' => ['client.email' => SORT_ASC],
                'desc' => ['client.email' => SORT_DESC],
            ],
            'birth_date' => [
                'asc' => ['client.birth_date' => SORT_ASC],
                'desc' => ['client.birth_date' => SORT_DESC],
            ],
            'created_at' => [
                'asc' => ['client.created_at' => SORT_ASC],
                'desc' => ['client.created_at' => SORT_DESC],
            ],
            'club.name' => [
                'asc' => ['club.name' => SORT_ASC],
                'desc' => ['club.name' => SORT_DESC],
            ],
        ];
    }
} 