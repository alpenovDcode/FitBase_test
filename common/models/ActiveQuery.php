<?php

namespace common\models;

use yii\db\ActiveQuery as BaseActiveQuery;
use yii\db\Expression;

/**
 * Расширенный класс ActiveQuery для реализации дополнительной функциональности
 */
class ActiveQuery extends BaseActiveQuery
{
    /**
     * @var bool Флаг для определения, показывать ли "удаленные" записи
     */
    protected $_showDeleted = false;
    
    /**
     * @var string Название атрибута, используемого для мягкого удаления
     */
    protected $_deletedAtAttribute = 'deleted_at';
    
    /**
     * @var string Имя таблицы для точной генерации SQL
     */
    protected $_tableName;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // Получаем имя таблицы из модели
        if ($this->modelClass) {
            $modelClass = $this->modelClass;
            $this->_tableName = $modelClass::tableName();
        }
    }

    /**
     * Устанавливает запрос для исключения "удаленных" записей
     * @return $this
     */
    public function notDeleted()
    {
        $tableName = $this->getTableName();
        $this->andWhere(["$tableName.{$this->_deletedAtAttribute}" => null]);
        
        return $this;
    }
    
    /**
     * Устанавливает запрос для включения только "удаленных" записей
     * @return $this
     */
    public function onlyDeleted()
    {
        $tableName = $this->getTableName();
        $this->andWhere(['IS NOT', "$tableName.{$this->_deletedAtAttribute}", null]);
        
        return $this;
    }
    
    /**
     * Устанавливает запрос для включения всех записей (включая "удаленные")
     * @return $this
     */
    public function withDeleted()
    {
        $this->_showDeleted = true;
        
        return $this;
    }
    
    /**
     * Устанавливает запрос для фильтрации по дате создания
     * @param string $date Дата в формате Y-m-d
     * @return $this
     */
    public function createdAt($date)
    {
        $tableName = $this->getTableName();
        $this->andWhere(new Expression("DATE($tableName.created_at) = :date"), [':date' => $date]);
        
        return $this;
    }
    
    /**
     * Устанавливает запрос для фильтрации по дате создания в диапазоне
     * @param string $from Дата начала в формате Y-m-d
     * @param string $to Дата окончания в формате Y-m-d
     * @return $this
     */
    public function createdBetween($from, $to)
    {
        $tableName = $this->getTableName();
        $this->andWhere(['between', "DATE($tableName.created_at)", $from, $to]);
        
        return $this;
    }
    
    /**
     * Переопределение метода подготовки запроса для автоматического исключения удаленных записей
     * @param \yii\db\QueryBuilder $builder
     * @return \yii\db\Query
     */
    public function prepare($builder)
    {
        if (!$this->_showDeleted) {
            $this->notDeleted();
        }
        
        return parent::prepare($builder);
    }
    
    /**
     * Получает имя таблицы для использования в запросах
     * @return string
     */
    protected function getTableName()
    {
        return $this->_tableName ?: ($this->modelClass ? call_user_func([$this->modelClass, 'tableName']) : '');
    }
} 