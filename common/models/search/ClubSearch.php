<?php

namespace common\models\search;

use common\models\Club;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * ClubSearch представляет модель для поиска по базе данных Club
 */
class ClubSearch extends Club
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
     * @var string Поле для фильтрации по дате создания от
     */
    public $createdAtFrom;
    
    /**
     * @var string Поле для фильтрации по дате создания до
     */
    public $createdAtTo;
    
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
            [['id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['name', 'address', 'phone', 'email', 'description', 'globalSearch'], 'string'],
            [['created_at', 'updated_at', 'deleted_at', 'createdAtFrom', 'createdAtTo'], 'safe'],
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
        $query = Club::find();
        $query->from(['club' => 'club']);
        $pageSize = isset($options['pageSize']) ? (int)$options['pageSize'] : $this->pageSize;

        // Настройка сортировки и пагинации
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
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
        
        // Показывать удаленные записи
        if ($this->showDeleted) {
            $query->withDeleted();
        }

        // Фильтрация по атрибутам
        $query->andFilterWhere([
            'club.id' => $this->id,
            'club.created_by' => $this->created_by,
            'club.updated_by' => $this->updated_by,
            'club.deleted_by' => $this->deleted_by,
        ]);
        
        // Фильтрация по дате создания
        if ($this->createdAtFrom) {
            $query->andFilterWhere(['>=', 'DATE(club.created_at)', date('Y-m-d', strtotime($this->createdAtFrom))]);
        }
        
        if ($this->createdAtTo) {
            $query->andFilterWhere(['<=', 'DATE(club.created_at)', date('Y-m-d', strtotime($this->createdAtTo))]);
        }
        
        // Фильтрация по конкретной дате
        if ($this->created_at) {
            $query->andFilterWhere(['=', new Expression('DATE(club.created_at)'), date('Y-m-d', strtotime($this->created_at))]);
        }
        
        if ($this->updated_at) {
            $query->andFilterWhere(['=', new Expression('DATE(club.updated_at)'), date('Y-m-d', strtotime($this->updated_at))]);
        }
        
        if ($this->deleted_at) {
            $query->andFilterWhere(['=', new Expression('DATE(club.deleted_at)'), date('Y-m-d', strtotime($this->deleted_at))]);
        }

        // Фильтрация по текстовым полям
        $query->andFilterWhere(['like', 'club.name', $this->name])
            ->andFilterWhere(['like', 'club.address', $this->address])
            ->andFilterWhere(['like', 'club.phone', $this->phone])
            ->andFilterWhere(['like', 'club.email', $this->email])
            ->andFilterWhere(['like', 'club.description', $this->description]);
            
        // Глобальный поиск по нескольким атрибутам
        if (!empty($this->globalSearch)) {
            $query->andWhere([
                'or',
                ['like', 'club.name', $this->globalSearch],
                ['like', 'club.address', $this->globalSearch],
                ['like', 'club.phone', $this->globalSearch],
                ['like', 'club.email', $this->globalSearch],
                ['like', 'club.description', $this->globalSearch],
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
                'asc' => ['club.name' => SORT_ASC],
                'desc' => ['club.name' => SORT_DESC],
            ],
            'address' => [
                'asc' => ['club.address' => SORT_ASC],
                'desc' => ['club.address' => SORT_DESC],
            ],
            'email' => [
                'asc' => ['club.email' => SORT_ASC],
                'desc' => ['club.email' => SORT_DESC],
            ],
            'created_at' => [
                'asc' => ['club.created_at' => SORT_ASC],
                'desc' => ['club.created_at' => SORT_DESC],
            ],
            'updated_at' => [
                'asc' => ['club.updated_at' => SORT_ASC],
                'desc' => ['club.updated_at' => SORT_DESC],
            ],
            'clientsCount' => [
                'asc' => ['(SELECT COUNT(*) FROM client WHERE client.club_id = club.id)' => SORT_ASC],
                'desc' => ['(SELECT COUNT(*) FROM client WHERE client.club_id = club.id)' => SORT_DESC],
            ],
        ];
    }
} 