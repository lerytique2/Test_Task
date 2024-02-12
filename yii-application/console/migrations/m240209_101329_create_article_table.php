<?php

use yii\db\Migration;

/**
 * Class m240209_101329_create_article_table
 */
class m240209_101329_create_article_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%article}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(0), // 0 - pending, 1 - published, 2 - rejected
            'author_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'published_at' => $this->integer()->defaultValue(null),
        ]);

        // Создание индекса для поля author_id
        $this->createIndex(
            '{{%idx-article-author_id}}',
            '{{%article}}',
            'author_id'
        );

        // Добавление внешнего ключа для author_id, связывающего с таблицей user
        $this->addForeignKey(
            '{{%fk-article-author_id}}',
            '{{%article}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        // Удаление внешнего ключа и индекса для author_id
        $this->dropForeignKey(
            '{{%fk-article-author_id}}',
            '{{%article}}'
        );

        $this->dropIndex(
            '{{%idx-article-author_id}}',
            '{{%article}}'
        );

        // Удаление таблицы article
        $this->dropTable('{{%article}}');
    }
}
