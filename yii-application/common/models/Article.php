<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $status
 * @property int $author_id
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $published_at
 *
 * @property User $author
 */
class Article extends ActiveRecord
{
    const int STATUS_PENDING = 0; // Ожидает модерации
    const int STATUS_PUBLISHED = 1; // Опубликовано
    const int STATUS_REJECTED = 2; // Отклонено

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%article}}';
    }

    /**
     * Behaviors
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Rules
     */
    public function rules(): array
    {
        return [
            [['title', 'content', 'author_id'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['content'], 'string'],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_PUBLISHED, self::STATUS_REJECTED]],
            [['author_id', 'created_at', 'updated_at', 'published_at'], 'integer'],
            [['author_id'], 'exist', 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * Relations
     */
    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'status' => 'Status',
            'author_id' => 'Author ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'published_at' => 'Published At',
        ];
    }

    /**
     * Помощник для проверки статуса статьи
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Одобряет статью и устанавливает дату публикации.
     */
    public function approve(): bool
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = time();
        return $this->save(false);
    }

    /**
     * Отклоняет статью.
     */
    public function reject(): bool
    {
        $this->status = self::STATUS_REJECTED;
        return $this->save(false);
    }
}
