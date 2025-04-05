<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Модель для таблицы "club".
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string|null $description
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $deleted_at
 * @property int|null $deleted_by
 *
 * @property Client[] $clients
 * @property User $createdBy
 * @property User $updatedBy
 * @property User $deletedBy
 * @property-read int $clientsCount Количество клиентов в клубе
 * @property-read string $phoneLink HTML-ссылка для телефона
 * @property-read string $emailLink HTML-ссылка для email
 */
class Club extends BaseModel
{
    /**
     * @var array Кэш для подсчета связанных записей
     */
    protected $_counters = [];
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'club';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'address', 'phone', 'email'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['name', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['phone'], 'match', 'pattern' => '/^\+?[0-9\s\-\(\)]{7,20}$/', 'message' => 'Некорректный формат телефона'],
            [['name'], 'unique', 'targetAttribute' => ['name', 'deleted_at'], 'filter' => ['deleted_at' => null], 'message' => 'Клуб с таким названием уже существует'],
            [['email'], 'unique', 'targetAttribute' => ['email', 'deleted_at'], 'filter' => ['deleted_at' => null], 'message' => 'Клуб с таким email уже существует'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'address' => 'Адрес',
            'phone' => 'Телефон',
            'email' => 'Email',
            'description' => 'Описание',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'created_by' => 'Кем создано',
            'updated_by' => 'Кем обновлено',
            'deleted_at' => 'Дата удаления',
            'deleted_by' => 'Кем удалено',
            'clientsCount' => 'Клиентов',
        ];
    }

    /**
     * Gets query for [[Clients]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClients()
    {
        return $this->hasMany(Client::class, ['club_id' => 'id']);
    }
    
    /**
     * Возвращает массив клубов для выпадающего списка
     * @param bool $includeDeleted Включать ли удаленные клубы
     * @param string $keyField Поле для использования в качестве ключа
     * @param string $valueField Поле для использования в качестве значения
     * @return array
     */
    public static function getDropdownList($includeDeleted = false, $keyField = 'id', $valueField = 'name')
    {
        $query = self::find();
        
        if (!$includeDeleted) {
            $query->notDeleted();
        }
        
        $clubs = $query->orderBy([$valueField => SORT_ASC])->all();
        
        return ArrayHelper::map($clubs, $keyField, $valueField);
    }
    
    /**
     * Возвращает количество клиентов в клубе
     * @param bool $useCache Использовать ли кэшированное значение
     * @return int
     */
    public function getClientsCount($useCache = true)
    {
        if (!isset($this->_counters['clients']) || !$useCache) {
            $this->_counters['clients'] = (int)$this->getClients()->count();
        }
        
        return $this->_counters['clients'];
    }
    
    /**
     * Возвращает HTML-ссылку для телефона
     * @return string
     */
    public function getPhoneLink()
    {
        if (empty($this->phone)) {
            return '';
        }
        
        $cleanPhone = preg_replace('/[^\d+]/', '', $this->phone);
        return Html::a($this->phone, 'tel:' . $cleanPhone);
    }
    
    /**
     * Возвращает HTML-ссылку для email
     * @return string
     */
    public function getEmailLink()
    {
        if (empty($this->email)) {
            return '';
        }
        
        return Html::a($this->email, 'mailto:' . $this->email);
    }
    
    /**
     * Находит модель по названию
     * @param string $name Название клуба
     * @param bool $includeDeleted Включать ли удаленные клубы
     * @return static|null
     */
    public static function findByName($name, $includeDeleted = false)
    {
        $query = static::find()->where(['name' => $name]);
        
        if ($includeDeleted) {
            $query->withDeleted();
        }
        
        return $query->one();
    }
}
