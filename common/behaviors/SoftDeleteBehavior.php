<?php

namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Поведение для реализации softу delete записей
 * 
 * @property-read boolean $isDeleted Флаг удаленной записи
 */
class SoftDeleteBehavior extends Behavior
{
    /**
     * @var string Атрибут для хранения даты удаления
     */
    public $deletedAtAttribute = 'deleted_at';
    
    /**
     * @var string Атрибут для хранения ID пользователя, удалившего запись
     */
    public $deletedByAttribute = 'deleted_by';
    
    /**
     * @var bool Использовать Expression('NOW()') вместо PHP date()
     */
    public $useDbExpression = true;
    
    /**
     * @var bool Флаг для определения, было ли выполнено мягкое удаление
     */
    private $_softDeleteDone = false;

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Обработка события перед удалением записи
     * @param \yii\base\Event $event
     * @return bool
     */
    public function beforeDelete($event)
    {
        $model = $this->owner;
        
        if (!$this->_softDeleteDone && 
            $model->hasAttribute($this->deletedAtAttribute) && 
            $model->{$this->deletedAtAttribute} === null) {
            
            // Помечаем запись как удаленную
            $attributes = [
                $this->deletedAtAttribute => $this->useDbExpression 
                    ? new Expression('NOW()') 
                    : date('Y-m-d H:i:s'),
            ];
            
            // Сохраняем ID пользователя, выполнившего удаление
            if ($model->hasAttribute($this->deletedByAttribute) && !Yii::$app->user->isGuest) {
                $attributes[$this->deletedByAttribute] = Yii::$app->user->id;
            }
            
            $result = $model->updateAttributes($attributes);
            
            if ($result) {
                $this->_softDeleteDone = true;
                $event->isValid = false; // Отменяем фактическое удаление из БД
            }
            
            // Возвращаем результат операции обновления атрибутов
            return $result;
        }
        
        return true;
    }

    /**
     * Обработка события после удаления записи
     * @param \yii\base\Event $event
     */
    public function afterDelete($event)
    {
        $this->_softDeleteDone = false;
    }

    /**
     * Восстановление "удаленной" записи
     * @return bool Результат восстановления
     */
    public function restore()
    {
        $model = $this->owner;
        
        if ($model->hasAttribute($this->deletedAtAttribute)) {
            $attributes = [
                $this->deletedAtAttribute => null,
            ];
            
            if ($model->hasAttribute($this->deletedByAttribute)) {
                $attributes[$this->deletedByAttribute] = null;
            }
            
            return $model->updateAttributes($attributes);
        }
        
        return false;
    }

    /**
     * Фактическое удаление записи из БД
     * @return bool|int Результат удаления
     */
    public function forceDelete()
    {
        $model = $this->owner;
        $this->_softDeleteDone = true;
        
        return $model->delete();
    }
    
    public function getIsDeleted()
    {
        $model = $this->owner;
        return $model->hasAttribute($this->deletedAtAttribute) && $model->{$this->deletedAtAttribute} !== null;
    }
} 