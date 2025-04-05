<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Модель для таблицы "client".
 *
 * @property int $id
 * @property int $club_id
 * @property string $name
 * @property string $surname
 * @property string $phone
 * @property string $email
 * @property string|null $birth_date
 * @property string|null $gender
 * @property string|null $address
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $deleted_at
 * @property int|null $deleted_by
 *
 * @property Club $club
 * @property User $createdBy
 * @property User $updatedBy
 * @property User $deletedBy
 * @property-read string $fullName Полное имя клиента
 * @property-read string $birthDateFormatted Отформатированная дата рождения
 * @property-read int|null $age Возраст клиента
 * @property-read string $genderText Текстовое представление пола
 * @property-read string $phoneLink HTML-ссылка для телефона
 * @property-read string $emailLink HTML-ссылка для email
 */
class Client extends BaseModel
{
    /**
     * Константы для пола клиента
     */
    const GENDER_MALE = 'M';
    const GENDER_FEMALE = 'F';
    
    /**
     * @var string Полное имя для поиска
     */
    public $fullNameSearch;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['club_id', 'name', 'surname', 'phone', 'email'], 'required'],
            [['club_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['birth_date', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'surname', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['gender'], 'string', 'max' => 1],
            [['gender'], 'in', 'range' => [self::GENDER_MALE, self::GENDER_FEMALE]],
            [['phone'], 'match', 'pattern' => '/^\+?[0-9\s\-\(\)]{7,20}$/', 'message' => 'Некорректный формат телефона'],
            [['birth_date'], 'date', 'format' => 'php:Y-m-d'],
            [['club_id'], 'exist', 'skipOnError' => true, 'targetClass' => Club::class, 'targetAttribute' => ['club_id' => 'id']],
            [['email'], 'unique', 'targetAttribute' => ['email', 'deleted_at'], 'filter' => ['deleted_at' => null], 'message' => 'Клиент с таким email уже существует'],
            [['fullNameSearch'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'club_id' => 'Клуб',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'phone' => 'Телефон',
            'email' => 'Email',
            'birth_date' => 'Дата рождения',
            'gender' => 'Пол',
            'address' => 'Адрес',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'created_by' => 'Кем создано',
            'updated_by' => 'Кем обновлено',
            'deleted_at' => 'Дата удаления',
            'deleted_by' => 'Кем удалено',
            'fullName' => 'Полное имя',
            'fullNameSearch' => 'Полное имя',
            'age' => 'Возраст',
        ];
    }

    /**
     * Gets query for [[Club]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClub()
    {
        return $this->hasOne(Club::class, ['id' => 'club_id']);
    }
    
    /**
     * Возвращает полное имя клиента
     * @param bool $lastNameFirst Фамилия первой
     * @return string
     */
    public function getFullName($lastNameFirst = true)
    {
        return $lastNameFirst 
            ? trim($this->surname . ' ' . $this->name)
            : trim($this->name . ' ' . $this->surname);
    }
    
    /**
     * Возвращает отформатированную дату рождения
     * @param string $format Формат даты
     * @return string
     */
    public function getBirthDateFormatted($format = 'dd.MM.yyyy')
    {
        if ($this->birth_date) {
            return Yii::$app->formatter->asDate($this->birth_date, $format);
        }
        
        return '';
    }
    
    /**
     * Возвращает возраст клиента
     * @return int|null
     */
    public function getAge()
    {
        if ($this->birth_date) {
            $birthDate = new \DateTime($this->birth_date);
            $now = new \DateTime();
            $interval = $now->diff($birthDate);
            
            return $interval->y;
        }
        
        return null;
    }
    
    /**
     * Возвращает текстовое представление пола
     * @return string
     */
    public function getGenderText()
    {
        $genders = self::getGenderList();
        
        return isset($genders[$this->gender]) ? $genders[$this->gender] : '';
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
     * Возвращает список полов для выпадающего списка
     * @return array
     */
    public static function getGenderList()
    {
        return [
            self::GENDER_MALE => 'Мужской',
            self::GENDER_FEMALE => 'Женский',
        ];
    }
    
    /**
     * Находит клиентов по названию клуба
     * @param string $clubName Название клуба
     * @param bool $includeDeleted Включать ли удаленные записи
     * @return static[]
     */
    public static function findByClubName($clubName, $includeDeleted = false)
    {
        $club = Club::findByName($clubName, $includeDeleted);
        
        if (!$club) {
            return [];
        }
        
        $query = static::find()
            ->where(['club_id' => $club->id])
            ->orderBy(['surname' => SORT_ASC, 'name' => SORT_ASC]);
        
        if ($includeDeleted) {
            $query->withDeleted();
        }
        
        return $query->all();
    }
    
    /**
     * Находит клиентов клуба
     * @param int $clubId ID клуба
     * @param bool $includeDeleted Включать ли удаленные записи
     * @return static[]
     */
    public static function findByClubId($clubId, $includeDeleted = false)
    {
        $query = static::find()
            ->where(['club_id' => $clubId])
            ->orderBy(['surname' => SORT_ASC, 'name' => SORT_ASC]);
        
        if ($includeDeleted) {
            $query->withDeleted();
        }
        
        return $query->all();
    }
    
    /**
     * Возвращает массив клиентов для выпадающего списка
     * @param bool $includeDeleted Включать ли удаленные записи
     * @param int|null $clubId ID клуба для фильтрации
     * @return array
     */
    public static function getDropdownList($includeDeleted = false, $clubId = null)
    {
        $query = static::find()->orderBy(['surname' => SORT_ASC, 'name' => SORT_ASC]);
        
        if ($clubId !== null) {
            $query->andWhere(['club_id' => $clubId]);
        }
        
        if (!$includeDeleted) {
            $query->notDeleted();
        }
        
        $clients = $query->all();
        
        return ArrayHelper::map($clients, 'id', function($model) {
            /* @var $model Client */
            return $model->getFullName();
        });
    }
}
