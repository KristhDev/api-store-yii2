<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%orders}}`.
 */
class m210911_164300_create_orders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(255)->notNull(),
            'product_id' => $this->integer(255)->notNull(),
            'direction' => $this->text()->notNull(),
            'quantity' => $this->integer(255)->notNull(),
            'total_to_pay' => $this->integer(255)->notNull(),
            'status' => "ENUM('Pendiente', 'Cancelado', 'Confirmado') DEFAULT 'Pendiente'",
            'date' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP()'),
            'date_confirm' => $this->dateTime()
        ]);

        $this->addForeignKey('fk_orders_user', '{{%orders}}', 'user_id', '{{%users}}', 'id');
        $this->addForeignKey('fk_orders_product', '{{%orders}}', 'product_id', '{{%products}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_orders_user', '{{%orders}}');
        $this->dropForeignKey('fk_orders_product', '{{%orders}}');
        $this->dropTable('{{%orders}}');
    }
}
