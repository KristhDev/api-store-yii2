<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%products}}`.
 */
class m210908_051942_create_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%products}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text()->notNull(),
            'price' => $this->float()->notNull(),
            'stock' => $this->integer()->notNull(),
            'image' => $this->string()->notNull(),

            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_product_category', '{{%products}}', 'category_id', '{{%categories}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_product_category', '{{%products}}');
        $this->dropTable('{{%products}}');
    }
}
