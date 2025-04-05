<?php

namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\base\Event;

/**
 * Поведение для автоматического заполнения временных меток
 */
class TimestampBehavior extends Behavior
{
    /**
     * @var string Атрибут для хранения даты создания
     */
    public $createdAtAttribute = 'created_at';
    
    /**
     * @var string Атрибут для хранения даты обновления
     */
    public $updatedAtAttribute = 'updated_at';
    
    /**
     * @var string Атрибут для хранения пользователя, создавшего запись
     */
    public $createdByAttribute = 'created_by';
    
    /**
     * @var string Атрибут для хранения пользователя, обновившего запись
     */
    public $updatedByAttribute = 'updated_by';
    
    /**
     * @var string|null Атрибут для хранения даты удаления (soft delete)
     */
    public $deletedAtAttribute = 'deleted_at';
    
    /**
     * @var string|null Атрибут для хранения пользователя, удалившего запись
     */
    public $deletedByAttribute = 'deleted_by';

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * Обработка события перед вставкой записи
     */
    public function beforeInsert(Event $event)
    {
        $model = $this->owner;
        
        if ($model->hasAttribute($this->createdAtAttribute) && empty($model->{$this->createdAtAttribute})) {
            $model->{$this->createdAtAttribute} = date('Y-m-d H:i:s');
        }
        
        if ($model->hasAttribute($this->updatedAtAttribute) && empty($model->{$this->updatedAtAttribute})) {
            $model->{$this->updatedAtAttribute} = date('Y-m-d H:i:s');
        }
        
        if ($model->hasAttribute($this->createdByAttribute) && empty($model->{$this->createdByAttribute}) && !Yii::$app->user->isGuest) {
            $model->{$this->createdByAttribute} = Yii::$app->user->id;
        }
        
        if ($model->hasAttribute($this->updatedByAttribute) && empty($model->{$this->updatedByAttribute}) && !Yii::$app->user->isGuest) {
            $model->{$this->updatedByAttribute} = Yii::$app->user->id;
        }
    }

    /**
     * Обработка события перед обновлением записи
     */
    public function beforeUpdate(Event $event)
    {
        $model = $this->owner;
        
        if ($model->hasAttribute($this->updatedAtAttribute)) {
            $model->{$this->updatedAtAttribute} = date('Y-m-d H:i:s');
        }
        
        if ($model->hasAttribute($this->updatedByAttribute) && !Yii::$app->user->isGuest) {
            $model->{$this->updatedByAttribute} = Yii::$app->user->id;
        }
    }

    /**
     * Обработка события перед удалением записи
     * Реализует "мягкое удаление" если определены соответствующие атрибуты
     */
    public function beforeDelete(Event $event)
    {
        $model = $this->owner;
        
        if ($model->hasAttribute($this->deletedAtAttribute) && $model->hasAttribute($this->deletedByAttribute)) {
            // Мягкое удаление
            $model->{$this->deletedAtAttribute} = date('Y-m-d H:i:s');
            
            if (!Yii::$app->user->isGuest) {
                $model->{$this->deletedByAttribute} = Yii::$app->user->id;
            }
            
            $model->update(false, [$this->deletedAtAttribute, $this->deletedByAttribute]);
            $event->isValid = false; // Отменяем реальное удаление
        }
    }
} 