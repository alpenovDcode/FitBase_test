<?php

namespace common\models;

use common\behaviors\SoftDeleteBehavior;
use common\behaviors\TimestampBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Базовый класс для всех моделей
 * 
 * @property-read \common\models\User $createdBy
 * @property-read \common\models\User $updatedBy
 * @property-read \common\models\User $deletedBy
 * @property-read string $createdAtFormatted
 * @property-read string $updatedAtFormatted
 * @property-read string $deletedAtFormatted
 * @property-read boolean $isDeleted
 */
class BaseModel extends ActiveRecord
{
    /**
     * @var array Текущие значения атрибутов до изменений
     */
    protected $_oldAttributes = [];
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            'softDelete' => [
                'class' => SoftDeleteBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find()
    {
        return new ActiveQuery(get_called_class());
    }
    
    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        parent::afterFind();
        
        // Сохраняем оригинальные значения для отслеживания изменений
        $this->_oldAttributes = $this->attributes;
    }
    
    /**
     * Возвращает массив изменений атрибутов
     * @return array Массив изменений в формате [атрибут => [старое значение, новое значение]]
     */
    public function getChangedAttributes()
    {
        $changedAttributes = [];
        
        foreach ($this->attributes as $name => $value) {
            if (isset($this->_oldAttributes[$name]) && $this->_oldAttributes[$name] !== $value) {
                $changedAttributes[$name] = [
                    'old' => $this->_oldAttributes[$name],
                    'new' => $value
                ];
            }
        }
        
        return $changedAttributes;
    }
    
    /**
     * Проверяет, является ли запись удаленной
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->hasAttribute('deleted_at') && $this->deleted_at !== null;
    }
    
    /**
     * Возвращает пользователя, создавшего запись
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if ($this->hasAttribute('created_by')) {
            return $this->hasOne(User::class, ['id' => 'created_by']);
        }
        
        return null;
    }
    
    /**
     * Возвращает пользователя, обновившего запись
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        if ($this->hasAttribute('updated_by')) {
            return $this->hasOne(User::class, ['id' => 'updated_by']);
        }
        
        return null;
    }
    
    /**
     * Возвращает пользователя, удалившего запись
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedBy()
    {
        if ($this->hasAttribute('deleted_by')) {
            return $this->hasOne(User::class, ['id' => 'deleted_by']);
        }
        
        return null;
    }
    
    /**
     * Форматирует дату с учетом настроек приложения
     * @param string $attribute Название атрибута с датой
     * @param string $format Формат даты
     * @return string
     */
    protected function formatDate($attribute, $format = 'dd.MM.yyyy HH:mm')
    {
        if ($this->hasAttribute($attribute) && !empty($this->$attribute)) {
            return Yii::$app->formatter->asDatetime($this->$attribute, $format);
        }
        
        return '';
    }
    
    /**
     * Возвращает отформатированную дату создания
     * @param string $format Формат даты
     * @return string
     */
    public function getCreatedAtFormatted($format = 'dd.MM.yyyy HH:mm')
    {
        return $this->formatDate('created_at', $format);
    }
    
    /**
     * Возвращает отформатированную дату обновления
     * @param string $format Формат даты
     * @return string
     */
    public function getUpdatedAtFormatted($format = 'dd.MM.yyyy HH:mm')
    {
        return $this->formatDate('updated_at', $format);
    }
    
    /**
     * Возвращает отформатированную дату удаления
     * @param string $format Формат даты
     * @return string
     */
    public function getDeletedAtFormatted($format = 'dd.MM.yyyy HH:mm')
    {
        return $this->formatDate('deleted_at', $format);
    }
    
    /**
     * Возвращает дружественное название модели для отображения
     * @return string
     */
    public static function modelName()
    {
        return Yii::t('app', static::className());
    }
    
    /**
     * Возвращает дружественное название атрибута для отображения
     * @param string $attribute Имя атрибута
     * @return string
     */
    public static function attributeName($attribute)
    {
        return ArrayHelper::getValue(static::instance()->attributeLabels(), $attribute, $attribute);
    }
    
    /**
     * Возвращает экземпляр модели для статических вызовов
     * @param bool $refresh Создать новый экземпляр
     * @return static
     */
    public static function instance($refresh = false)
    {
        return new static();
    }
} 