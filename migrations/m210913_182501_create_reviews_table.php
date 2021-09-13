<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reviews}}`.
 */
class m210913_182501_create_reviews_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%reviews}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'comment' => $this->text()->notNull(),
            'starts' => "ENUM('0', '1', '2', '3', '4', '5') NOT NULL",
            'status' => "ENUM('1', '0') DEFAULT '1'",
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_review_user', '{{%reviews}}', 'user_id', '{{%users}}', 'id');
        $this->addForeignKey('fk_review_product', '{{%reviews}}', 'product_id', '{{%products}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_review_user', '{{%reviews}}');
        $this->dropForeignKey('fk_review_product', '{{%reviews}}');
        $this->dropTable('{{%reviews}}');
    }
}
